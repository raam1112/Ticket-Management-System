<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('generated_by');
            $table->string('name', 255);
            $table->string('type', 100);
            $table->json('filters')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->enum('format', ['pdf', 'excel', 'csv'])->default('pdf');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('generated_by', 'idx_report_user');
            $table->foreign('generated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
