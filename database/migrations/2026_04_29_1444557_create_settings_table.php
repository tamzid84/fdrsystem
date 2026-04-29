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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('tax_rate', 5, 2)->default(10);

            $table->json('duty_slabs')->nullable();
    /*
    Example JSON:
    [
        {"min":0,"max":100000,"amount":0},
        {"min":100001,"max":500000,"amount":500},
        {"min":500001,"max":1000000,"amount":1500},
        {"min":1000001,"max":999999999,"amount":2500}
    ]
    */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
