<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue_id', 255)->nullable();
            $table->string('sender', 255);
            $table->string('recipient', 255);
            $table->string('subject', 500)->nullable();
            $table->bigInteger('size')->default(0);
            $table->enum('status', ['queued', 'active', 'deferred', 'hold', 'bounced', 'sent'])->default('queued');
            $table->integer('attempts')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['queue_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_logs');
    }
};