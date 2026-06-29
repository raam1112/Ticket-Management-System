<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->unique()->comment('TKT-2026-000001');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('priority_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('sla_policy_id')->nullable();
            $table->string('title', 255);
            $table->longText('description');
            $table->enum('status', [
                'open', 'assigned', 'in_progress', 'pending_user',
                'escalated', 'under_review', 'resolved', 'closed',
                'reopened', 'cancelled'
            ])->default('open');
            $table->text('resolution_note')->nullable();
            $table->enum('resolution_level', ['immediate', 'standard', 'delayed', 'escalated'])->nullable();
            $table->timestamp('sla_response_at')->nullable()->comment('First response due');
            $table->timestamp('sla_resolve_at')->nullable()->comment('Resolution due');
            $table->boolean('sla_response_met')->nullable();
            $table->boolean('sla_resolve_met')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->tinyInteger('reopen_count')->unsigned()->default(0);
            $table->tinyInteger('escalation_count')->unsigned()->default(0);
            $table->tinyInteger('satisfaction_rating')->unsigned()->nullable()->comment('1-5 stars');
            $table->text('satisfaction_note')->nullable();
            $table->enum('source', ['web', 'email', 'api', 'phone'])->default('web');
            $table->boolean('is_internal')->default(false);
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('status', 'idx_ticket_status');
            $table->index('created_by', 'idx_ticket_created_by');
            $table->index('assigned_to', 'idx_ticket_assigned');
            $table->index('category_id', 'idx_ticket_cat');
            $table->index('priority_id', 'idx_ticket_prio');
            $table->index('department_id', 'idx_ticket_dept');
            $table->index('sla_response_at', 'idx_ticket_sla_resp');
            $table->index('sla_resolve_at', 'idx_ticket_sla_res');
            $table->index('created_at', 'idx_ticket_created');

            // Foreign keys
            $table->foreign('category_id')->references('id')->on('ticket_categories');
            $table->foreign('priority_id')->references('id')->on('ticket_priorities');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('sla_policy_id')->references('id')->on('sla_policies')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
