<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_health_endpoint_returns_ok()
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson(['status' => 'ok']);
    }

    public function test_can_create_wallet()
    {
        $payload = [
            'owner_name' => 'My Wallet',
            'currency' => 'USD',
        ];

        $response = $this->postJson('/api/wallets', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('wallets', [
            'owner_name' => 'My Wallet',
            'currency' => 'USD',
        ]);
    }


    public function test_can_list_wallets()
    {
        Wallet::factory(2)->create();

        $response = $this->getJson('/api/wallets');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }


    public function test_can_view_wallet()
    {
        $wallet = \App\Models\Wallet::factory()->create();

        $response = $this->getJson("/api/wallets/{$wallet->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $wallet->id]);
    }

    public function test_can_get_wallet_balance_0()
    {
        $wallet = Wallet::factory()->create();

        $response = $this->getJson("/api/wallets/{$wallet->id}/balance");

        $response->assertStatus(200)
            ->assertJson(['balance' => 0]);

        $this->assertDatabaseHas('wallets', [
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

        $response->assertStatus(201);

        $this->assertDatabaseHas('wallets', [
            'balance' => 100 * 100,
        ]);
    }

    public function test_can_withdraw_from_wallet_balance()
    {
        $wallet = Wallet::factory()->create([
            'balance' => 100 * 100 // save as cents
        ]);

        $payload = [
            'amount' => 100
        ];

        $response = $this->withHeaders([
            'Idempotency-Key' => 'unique-key-123',
        ])->postJson("/api/wallets/{$wallet->id}/withdraw", $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('wallets', [
            'balance' => 0,
        ]);
    }


    public function test_can_get_wallet_transactions()
    {
        $wallet = Wallet::factory()->create();
        Transaction::factory()->count(3)->create(['wallet_id' => $wallet->id]);

        $response = $this->getJson("/api/wallets/{$wallet->id}/transactions");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
