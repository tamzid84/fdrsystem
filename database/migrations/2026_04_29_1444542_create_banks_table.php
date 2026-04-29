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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['govt', 'private'])->default('private');

            $table->string('branch_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('routing_number')->nullable();

            $table->string('phone')->nullable();
            $table->string('address')->nullable();

            $table->decimal('total_investment', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
