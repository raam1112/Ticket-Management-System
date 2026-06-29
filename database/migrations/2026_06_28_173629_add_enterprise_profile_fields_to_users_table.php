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
        Schema::table('users', function (Blueprint $table) {
            $table->string('availability_status')->default('offline')->after('is_active');
            $table->string('preferred_language')->nullable()->after('location');
            $table->string('time_zone')->nullable()->after('preferred_language');
            $table->integer('agent_capacity')->default(10)->after('availability_status');
            $table->timestamp('last_activity_at')->nullable()->after('last_login_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'availability_status', 'preferred_language', 'time_zone', 'agent_capacity', 'last_activity_at'
            ]);
        });
    }
};
