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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fdr_id')->constrained()->cascadeOnDelete();

            $table->decimal('interest_amount', 18, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('tax_amount', 18, 2);

            $table->date('deduction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
