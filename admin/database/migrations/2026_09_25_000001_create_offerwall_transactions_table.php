<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offerwall_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('transaction_id', 191);
            $table->string('transaction_key', 64);
            $table->string('offer_id')->nullable();
            $table->string('offer_name')->nullable();
            $table->string('status', 40);
            $table->timestamp('completed_at')->nullable();
            $table->json('provider_payload')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'transaction_key']);
            $table->index(['user_id', 'completed_at']);
            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offerwall_transactions');
    }
};
