<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(array_column(TransactionType::cases(), 'value')),
            'amount' => $this->faker->numberBetween(100, 10000), // in minor units (cents)
            'related_wallet_id' => $this->faker->boolean(30) ? Wallet::factory()->create()->id : null,
            'idempotency_key' => Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
