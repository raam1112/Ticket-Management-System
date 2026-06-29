<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('low|medium|high|critical');
            $table->string('display_name', 80);
            $table->string('color', 7)->comment('Hex badge color');
            $table->string('icon', 60)->nullable();
            $table->smallInteger('sla_hours_response')->unsigned()->comment('Hours to first response');
            $table->smallInteger('sla_hours_resolve')->unsigned()->comment('Hours to resolution');
            $table->smallInteger('sort_order')->unsigned()->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_priorities');
    }
};
