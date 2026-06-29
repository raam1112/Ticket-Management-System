<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_escalations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('escalated_by');
            $table->unsignedBigInteger('escalated_to')->nullable();
            $table->text('reason');
            $table->enum('escalation_type', ['manual', 'auto_sla'])->default('manual');
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('ticket_id', 'idx_esc_ticket');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('escalated_by')->references('id')->on('users');
            $table->foreign('escalated_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_escalations');
    }
};
