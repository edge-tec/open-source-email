<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->enum('role', ['super_admin', 'admin', 'reseller', 'user'])->default('user');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->integer('max_domains')->default(1);
            $table->integer('max_mailboxes')->default(10);
            $table->integer('max_quota')->default(1024)->comment('Max storage in MB');
            $table->string('avatar', 255)->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->string('language', 10)->default('en');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->bigInteger('parent_id')->unsigned()->nullable()->comment('Reseller parent ID');
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role']);
            $table->index(['status']);
            $table->index(['parent_id']);

            $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};