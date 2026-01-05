<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Enums\TransactionType;
use Illuminate\Support\Facades\DB;
use LogicException;

class WalletService
{
    public function deposit(Wallet $wallet, int $amount, string $key): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $key) {

            $existing = Transaction::where('wallet_id', $wallet->id)
                ->where('idempotency_key', $key)
                ->first();

            if ($existing) return $existing;

            $wallet->increment('balance', $amount);

            return Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => TransactionType::Deposit,
                'amount' => $amount,
                'idempotency_key' => $key,
            ]);
        });
    }

    public function withdraw(Wallet $wallet, int $amount, string $key): Transaction
    {
        return DB::transaction(function () use ($wallet, $amount, $key) {

            $existing = Transaction::where('wallet_id', $wallet->id)
                ->where('idempotency_key', $key)
                ->first();

            if ($existing) return $existing;

            if ($wallet->balance < $amount) {
                throw new LogicException('Insufficient funds');
            }

            $wallet->decrement('balance', $amount);

            return Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => TransactionType::Withdrawal,
                'amount' => $amount,
                'idempotency_key' => $key,
            ]);
        });
    }

    public function transfer(Wallet $from, Wallet $to, int $amount, string $key)
    {
        return DB::transaction(function () use ($from, $to, $amount, $key) {
            // Idempotency: check global
            $existing = Transaction::where('idempotency_key', $key)->first();
            if ($existing) return $existing;

            if ($from->id === $to->id) {
                throw new LogicException('Cannot transfer to the same wallet');
            }

            if ($from->currency !== $to->currency) {
                throw new LogicException('Currency mismatch');
            }

            if ($from->balance < $amount) {
                throw new LogicException('Insufficient funds');
            }

            $from->decrement('balance', $amount);
            $to->increment('balance', $amount);

            Transaction::insert([
                [
                    'wallet_id' => $from->id,
                    'type' => TransactionType::TransferDebit->value,
                    'amount' => $amount,
                    'related_wallet_id' => $to->id,
                    'idempotency_key' => $key,
                ],
                [
                    'wallet_id' => $to->id,
                    'type' => TransactionType::TransferCredit->value,
                    'amount' => $amount,
                    'related_wallet_id' => $from->id,
                    'idempotency_key' => $key,
                ],
            ]);
        });
    }
}
