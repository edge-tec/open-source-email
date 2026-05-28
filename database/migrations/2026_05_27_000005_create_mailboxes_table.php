<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailboxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('domain_id')->unsigned();
            $table->string('local_part', 255)->comment('Username before @');
            $table->string('email', 255)->unique();
            $table->string('password', 255)->comment('Hashed password for IMAP/SMTP auth');
            $table->string('name', 255)->nullable();
            $table->integer('quota')->default(1024)->comment('Quota in MB');
            $table->bigInteger('used_quota')->default(0)->comment('Used storage in bytes');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('maildir', 255)->comment('Maildir path');
            $table->text('signature')->nullable();
            $table->boolean('is_catchall')->default(false);
            $table->boolean('send_only')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->integer('msg_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id']);
            $table->index(['domain_id']);
            $table->unique(['local_part', 'domain_id']);
            $table->index(['status']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailboxes');
    }
};