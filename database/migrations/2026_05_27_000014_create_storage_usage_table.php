<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_usage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mailbox_id')->unsigned();
            $table->bigInteger('domain_id')->unsigned();
            $table->bigInteger('used_bytes')->default(0);
            $table->integer('message_count')->default(0);
            $table->bigInteger('quota_bytes')->default(0);
            $table->decimal('percentage_used', 5, 2)->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['mailbox_id']);
            $table->index(['domain_id']);

            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_usage');
    }
};