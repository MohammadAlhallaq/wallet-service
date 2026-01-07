<?php


namespace App\ValueObjects;

final class Money
{
    private int $amount;
    private string $currency;

    public function __construct(int $amount, string $currency = 'USD')
    {
        $this->amount   = $amount;
        $this->currency = strtoupper($currency);
    }

    public static function fromFloat(float $value, string $currency = 'USD'): self
    {
        return new self((int) round($value * 100), $currency);
    }

    public static function fromCents(int $cents, string $currency = 'USD'): self
    {
        return new self($cents, $currency);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function toFloat(): float
    {
        return $this->amount / 100;
    }

    public function __toString(): string
    {
        return number_format($this->toFloat(), 2) . ' ' . $this->currency;
    }
}
