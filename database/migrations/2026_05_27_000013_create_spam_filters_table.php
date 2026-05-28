<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spam_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mailbox_id')->unsigned()->nullable();
            $table->bigInteger('domain_id')->unsigned()->nullable();
            $table->enum('filter_type', ['whitelist', 'blacklist', 'custom_rule']);
            $table->string('value', 255)->comment('Email address or domain pattern');
            $table->enum('action', ['allow', 'reject', 'quarantine', 'tag'])->default('reject');
            $table->integer('priority')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['mailbox_id']);
            $table->index(['domain_id']);
            $table->index(['filter_type']);

            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spam_filters');
    }
};