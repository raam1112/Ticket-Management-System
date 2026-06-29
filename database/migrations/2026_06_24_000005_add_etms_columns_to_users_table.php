<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modify the default users table to add ETMS-specific columns.
     * The base users table is created by Laravel's default migration.
     * This migration adds department, phone, avatar, role, and status columns.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('id');
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar', 255)->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->index('department_id', 'idx_user_dept');
            $table->softDeletes(); // adds deleted_at for soft delete support
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'phone', 'avatar', 'is_active', 'last_login_at', 'last_login_ip']);
        });
    }
};
