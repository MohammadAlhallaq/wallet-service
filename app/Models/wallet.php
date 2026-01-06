<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

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

    public function deposit(float $amount): self
    {
        $this->increment('balance', $this->toMinorUnits($amount));

        return $this;
    }

    public function withdraw(float $amount): self
    {
        $amountInCents = $this->toMinorUnits($amount);

        $this->decrement('balance', $amountInCents);

        return $this;
    }


    protected function toMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    public function getBalanceAttribute($value): float
    {
        return $value / 100;
    }
}
