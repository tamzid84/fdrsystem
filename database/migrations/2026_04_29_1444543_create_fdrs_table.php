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
        Schema::create('fdrs', function (Blueprint $table) {
            $table->id();
            $table->string('fdr_number')->unique();
            $table->string('fdr_account_number')->nullable();

            $table->foreignId('fund_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();

            $table->string('branch_name')->nullable();

            $table->decimal('amount', 18, 2);
            $table->decimal('interest_rate', 5, 2);

            $table->date('start_date');
            $table->date('maturity_date');
            $table->integer('tenure'); // months বা days

            $table->enum('renewal_type', ['principal', 'principal_interest'])->default('principal');

            $table->enum('status', ['active', 'renewed', 'encashed'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fdrs');
    }
};
