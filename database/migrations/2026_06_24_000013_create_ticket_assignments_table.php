<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('assigned_to');
            $table->unsignedBigInteger('assigned_by');
            $table->enum('action', ['assigned', 'accepted', 'rejected', 'transferred', 'escalated'])
                  ->default('assigned');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('ticket_id', 'idx_assign_ticket');
            $table->index('assigned_to', 'idx_assign_to');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('assigned_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_assignments');
    }
};
