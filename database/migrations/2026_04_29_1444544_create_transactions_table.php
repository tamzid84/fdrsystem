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

    $table->foreignId('fdr_id')
        ->constrained()
        ->cascadeOnDelete();

    // 🔥 Better naming: include "interest" as separate type later if needed
    $table->enum('type', [
        'create',
        'renew',
        'encash',
        'interest',
        'tax',
        'duty'
    ]);

    $table->decimal('principal', 18, 2)->default(0);
    $table->decimal('interest', 18, 2)->default(0);
    $table->decimal('tax', 18, 2)->default(0);
    $table->decimal('duty', 18, 2)->default(0);

    $table->decimal('net_amount', 18, 2)->default(0);

    $table->dateTime('transaction_date')->useCurrent();

    $table->text('remarks')->nullable();

    // 🔥 Important for audit system
    $table->timestamps();

    // Optional but VERY useful for performance
    $table->index(['fdr_id', 'type']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
