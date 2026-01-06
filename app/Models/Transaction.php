<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'related_wallet_id',
        'idempotency_key'
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'amount' => 'integer',
    ];

    protected function balanceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attributes) => (int) $attributes['amount'] / 100,
            set: fn($value) => (int) $value * 100,
        );
    }
}
