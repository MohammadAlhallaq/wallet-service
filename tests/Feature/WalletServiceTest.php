<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_health_endpoint_returns_ok()
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['status' => 'ok']);
    }

    public function test_can_create_wallet()
    {
        $payload = [
            'owner_name' => 'My Wallet',
            'currency' => 'USD',
        ];

        $response = $this->postJson('/api/wallets', $payload);

        $response->assertSuccessful();

        $this->assertDatabaseHas('wallets', [
            'owner_name' => 'My Wallet',
            'currency' => 'USD',
        ]);
    }


    public function test_can_list_wallets()
    {
        Wallet::factory(2)->create();

        $response = $this->getJson('/api/wallets');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data');
    }


    public function test_can_view_wallet()
    {
        $wallet = Wallet::factory()->create();

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['id' => $wallet->id]);
    }

    public function test_can_get_wallet_balance_0()
    {
        $wallet = Wallet::factory()->create();

        $response = $this->getJson("/api/wallets/{$wallet->id}/balance");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['balance' => 0]);

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 0,
        ]);
    }

    public function test_can_deposit_to_wallet_balance()
    {
        $wallet = Wallet::factory()->create();

        $payload = [
            'amount' => 100
        ];
        $response = $this->withHeaders([
            'Idempotency-Key' => 'unique-key-123',
        ])->postJson("/api/wallets/{$wallet->id}/deposit", $payload);

        $response->assertSuccessful();

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 100 * 100,
        ]);
    }

    public function test_can_withdraw_from_wallet_balance()
    {
        $wallet = Wallet::factory()->create([
            'balance' => 100 * 100
        ]);

        $payload = [
            'amount' => 100
        ];

        $response = $this->withHeaders([
            'Idempotency-Key' => 'unique-key-123',
        ])->postJson("/api/wallets/{$wallet->id}/withdraw", $payload);

        $response->assertSuccessful();

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 0,
        ]);
    }


    public function test_can_get_wallet_transactions()
    {
        $wallet = Wallet::factory()->create();
        Transaction::factory()->count(3)->create(['wallet_id' => $wallet->id]);

        $response = $this->getJson("/api/wallets/{$wallet->id}/transactions");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data');
    }

    public function test_deposit_requires_idempotency_key()
    {
        $wallet = Wallet::factory()->create();

        $response = $this->postJson("/api/wallets/{$wallet->id}/deposit", [
            'amount' => 100,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_wallet_deposit()
    {
        $wallet = Wallet::factory()->create(['balance' => 100 * 100]);
        $key = 'unique-key-123';

        $response = $this->postJson("/api/wallets/{$wallet->id}/deposit", ['amount' => 50], [
            'Idempotency-Key' => $key
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 150 * 100,
        ]);

        // Retry with same key
        $response = $this->postJson("api/wallets/{$wallet->id}/deposit", ['amount' => 50], [
            'Idempotency-Key' => $key
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 150 * 100,
        ]);
    }


    public function test_wallet_withdraw_insufficient_funds()
    {
        $wallet = Wallet::factory()->create(['balance' => 50 * 100]);
        $key = 'withdraw-key-123';

        $response = $this->postJson("/api/wallets/{$wallet->id}/withdraw", ['amount' => 100], [
            'Idempotency-Key' => $key
        ]);

        $response->assertStatus(Response::HTTP_CONFLICT);
    }

    public function test_transfer_success()
    {
        $from = Wallet::factory()->create(['balance' => 100 * 100, 'currency' => 'USD']);
        $to = Wallet::factory()->create(['balance' => 50 * 100, 'currency' => 'USD']);
        $key = 'transfer-key-123';

        $response = $this->postJson("/api/transfers", [
            'from_wallet_id' => $from->id,
            'to_wallet_id' => $to->id,
            'amount' => 60
        ], [
            'Idempotency-Key' => $key
        ]);

        $response->assertOk();

        // Check that the source wallet balance decreased
        $this->assertDatabaseHas('wallets', [
            'id' => $from->id,
            'balance' => 40 * 100,
        ]);

        // Check that the destination wallet balance increased
        $this->assertDatabaseHas('wallets', [
            'id' => $to->id,
            'balance' => 110 * 100,
        ]);
    }
}
