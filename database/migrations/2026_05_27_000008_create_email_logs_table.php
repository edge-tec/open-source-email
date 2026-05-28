<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mailbox_id')->unsigned()->nullable();
            $table->bigInteger('domain_id')->unsigned()->nullable();
            $table->string('message_id', 255)->nullable();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('sender', 255);
            $table->string('recipient', 255);
            $table->string('subject', 500)->nullable();
            $table->bigInteger('size')->default(0)->comment('Size in bytes');
            $table->enum('status', ['queued', 'sent', 'delivered', 'deferred', 'bounced', 'rejected', 'spam'])->default('queued');
            $table->string('dsn_code', 20)->nullable();
            $table->text('error_message')->nullable();
            $table->string('client_ip', 45)->nullable();
            $table->string('server_ip', 45)->nullable();
            $table->decimal('spam_score', 5, 2)->nullable();
            $table->boolean('is_spam')->default(false);
            $table->boolean('has_attachment')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['mailbox_id']);
            $table->index(['domain_id']);
            $table->index(['direction']);
            $table->index(['status']);
            $table->index(['sender']);
            $table->index(['recipient']);
            $table->index(['created_at']);
            $table->index(['message_id']);

            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('set null');
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};