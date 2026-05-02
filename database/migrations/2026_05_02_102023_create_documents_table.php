<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->string('doc_no')->unique();

            // ✅ FIXED: specify correct table name
            $table->foreignId('template_id')
                ->constrained('document_templates')
                ->cascadeOnDelete();

            $table->json('data');

            $table->string('status')->default('draft');

            $table->foreignId('created_by');
            $table->foreignId('approved_by')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->string('qr_code')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};