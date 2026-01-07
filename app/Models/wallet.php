<?php

namespace App\Models;

use App\ValueObjects\Money;
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
        $this->increment('balance', Money::fromFloat($amount)->amount());

        return $this;
    }

    public function withdraw(float $amount): self
    {
        $amountInCents = Money::fromFloat($amount)->amount();

        $this->decrement('balance', $amountInCents);

        return $this;
    }

    public function getBalanceAttribute($value): float
    {
        return  Money::fromCents($value)->toFloat();
    }
}
