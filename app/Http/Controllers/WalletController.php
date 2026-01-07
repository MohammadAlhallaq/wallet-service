<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Throwable;

class WalletController extends Controller
{
    public function __construct(private WalletService $service) {}

    public function store(Request $request): JsonResource
    {
        $data = $request->validate([
            'owner_name' => 'required|string',
            'currency' => 'required|string|size:3',
        ]);

        $wallet =  Wallet::create($data);

        return WalletResource::make($wallet);
    }

    public function index(Request $request): JsonResource
    {
        $wallets =  Wallet::query()
            ->when($request->owner_name, fn($q) => $q->where('owner_name', $request->owner_name))
            ->when($request->currency, fn($q) => $q->where('currency', $request->currency))
            ->get();

        return WalletResource::collection($wallets);
    }

    public function show(Wallet $wallet)
    {
        return WalletResource::make($wallet);
    }

    public function balance(Wallet $wallet)
    {
        return [
            'balance' => $wallet->balance,
            'currency' => $wallet->currency,
        ];
    }

    public function transactions(Wallet $wallet, Request $request): JsonResource
    {
        $transactions = $wallet->transactions()
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->with(['Wallet', 'relatedWallet'])
            ->orderBy('created_at')
            ->simplePaginate(20);

        return TransactionResource::collection($transactions);
    }

    public function deposit(Wallet $wallet, Request $request): JsonResource
    {
        $request->validate(['amount' => 'required|integer|min:1']);

        $transaction = $this->service->deposit(
            $wallet,
            $request->integer('amount'),
            $request->header('Idempotency-Key')
        );

        return TransactionResource::make($transaction);
    }

    public function withdraw(Request $request, Wallet $wallet)
    {
        $request->validate(['amount' => 'required|integer|min:1']);

        return rescue(
            function () use ($wallet, $request) {
                $transaction = $this->service->withdraw(
                    $wallet,
                    $request->integer('amount'),
                    $request->header('Idempotency-Key')
                );
                return TransactionResource::make($transaction);
            },
            function (Exception $exception) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], Response::HTTP_CONFLICT);
            }
        );
    }
}
