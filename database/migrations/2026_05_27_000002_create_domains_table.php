<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('domain', 255)->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->integer('max_mailboxes')->default(10);
            $table->integer('max_aliases')->default(50);
            $table->integer('max_quota')->default(10240)->comment('Max storage in MB');
            $table->string('catch_all', 255)->nullable()->comment('Catch-all email address');
            $table->boolean('dkim_enabled')->default(false);
            $table->string('dkim_selector', 63)->default('edgemail');
            $table->text('dkim_private_key')->nullable();
            $table->text('dkim_public_key')->nullable();
            $table->string('spf_record', 500)->nullable();
            $table->string('dmarc_record', 500)->nullable();
            $table->string('transport', 255)->default('virtual');
            $table->boolean('is_backup_mx')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id']);
            $table->index(['status']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};