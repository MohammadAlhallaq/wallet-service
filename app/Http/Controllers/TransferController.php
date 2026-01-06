<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function __construct(private WalletService $service) {}

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'from_wallet_id' => 'required|exists:wallets,id',
            'to_wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|integer|min:1',
        ]);

        $this->service->transfer(
            Wallet::findOrFail($data['from_wallet_id']),
            Wallet::findOrFail($data['to_wallet_id']),
            $data['amount'],
            $request->header('Idempotency-Key')
        );

        return response()->json([
            "message" => 'transfer has been completed successfully'
        ]);
    }
}
