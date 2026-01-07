<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    public function wallet()
    {
        return $this->belongsTo(Wallet::class)->select('id', 'owner_name', 'balance', 'currency');
    }

    public function relatedWallet()
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id')->select('id', 'owner_name', 'balance', 'currency');
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attributes) => (int) $attributes['amount'] / 100,
            set: fn($value) => (int) $value * 100,
        );
    }
}
