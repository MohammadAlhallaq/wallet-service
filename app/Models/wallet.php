<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'owner_name',
        'currency',
        'balance',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    protected $attributes = [
        'balance' => 0,
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    protected function balanceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attributes) => (int) $attributes['balance'] / 100,
            set: fn($value) => (int) $value * 100,
        );
    }
}
