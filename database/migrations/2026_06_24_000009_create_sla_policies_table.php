<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('priority_id')->nullable();
            $table->smallInteger('response_time_hours')->unsigned();
            $table->smallInteger('resolution_time_hours')->unsigned();
            $table->smallInteger('escalation_after_hours')->unsigned()->default(0);
            $table->boolean('business_hours_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('ticket_categories')->onDelete('set null');
            $table->foreign('priority_id')->references('id')->on('ticket_priorities')->onDelete('set null');
            $table->index('category_id', 'idx_sla_cat');
            $table->index('priority_id', 'idx_sla_prio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
