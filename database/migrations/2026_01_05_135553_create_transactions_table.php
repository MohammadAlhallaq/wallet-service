<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('type', 32);
            $table->unsignedBigInteger('amount');
            $table->foreignId('related_wallet_id')->nullable()->constrained('wallets');
            $table->string('idempotency_key');
            $table->unique(['wallet_id', 'idempotency_key']);
            $table->timestamps();
        });
    }
};
