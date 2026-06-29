<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('For threaded replies');
            $table->longText('body');
            $table->boolean('is_internal')->default(false)->comment('1 = internal note, not visible to user');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ticket_id', 'idx_comment_ticket');
            $table->index('user_id', 'idx_comment_user');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('parent_id')->references('id')->on('ticket_comments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};
