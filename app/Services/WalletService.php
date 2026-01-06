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
            $wallet->lockForUpdate();

            $existing = Transaction::where('wallet_id', $wallet->id)
                ->where('idempotency_key', $key)
                ->first();

            if ($existing) return $existing;

            $wallet->increment('balance', $this->toCents($amount));

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
            $wallet->lockForUpdate();

            $existing = Transaction::where('wallet_id', $wallet->id)
                ->where('idempotency_key', $key)
                ->first();

            if ($existing) return $existing;

            if ($wallet->balance < $amount) {
                throw new LogicException('Insufficient funds');
            }

            $wallet->withdraw($amount);

            return Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => TransactionType::Withdrawal,
                'amount' => $amount,
                'idempotency_key' => $key,
            ]);
        });
    }

    public function transfer(Wallet $fromWallet, Wallet $toWallet, int $amount, string $key)
    {
        return DB::transaction(function () use ($fromWallet, $toWallet, $amount, $key) {

            $fromWallet->lockforupdate();
            $toWallet->lockforupdate();

            $existing = Transaction::where('idempotency_key', $key)->first();
            if ($existing) return $existing;

            if ($fromWallet->id === $toWallet->id) {
                throw new LogicException('Cannot transfer to the same wallet');
            }

            if ($fromWallet->currency !== $toWallet->currency) {
                throw new LogicException('Currency mismatch');
            }

            if ($fromWallet->balance < $amount) {
                throw new LogicException('Insufficient funds');
            }

            $fromWallet->withdraw($amount);
            $toWallet->deposit($amount);

            $debit = new Transaction([
                'wallet_id' => $fromWallet->id,
                'type' => TransactionType::TransferDebit,
                'amount' => $amount,
                'related_wallet_id' => $toWallet->id,
                'idempotency_key' => $key,
            ]);

            $credit = new Transaction([
                'wallet_id' => $toWallet->id,
                'type' => TransactionType::TransferCredit,
                'amount' => $amount,
                'related_wallet_id' => $fromWallet->id,
                'idempotency_key' => $key,
            ]);

            $debit->save();
            $credit->save();
            return;
        });
    }


    protected function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
