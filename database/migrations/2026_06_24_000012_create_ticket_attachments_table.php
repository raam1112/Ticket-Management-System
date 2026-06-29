<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('comment_id')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->string('filename', 255)->comment('Stored filename (UUID-based)');
            $table->string('original_name', 255)->comment('Original upload filename');
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size')->comment('Bytes');
            $table->string('disk', 50)->default('local');
            $table->string('path', 500);
            $table->timestamp('created_at')->useCurrent();

            $table->index('ticket_id', 'idx_att_ticket');
            $table->index('comment_id', 'idx_att_comment');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('comment_id')->references('id')->on('ticket_comments')->onDelete('set null');
            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};
