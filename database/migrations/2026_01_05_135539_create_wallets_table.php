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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name');
            $table->string('currency', 3);
            $table->unsignedBigInteger('balance')->default(0);
            $table->timestamps();
        });
    }
};
