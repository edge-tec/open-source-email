<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forwarding_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mailbox_id')->unsigned();
            $table->string('destination', 255);
            $table->boolean('keep_copy')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['mailbox_id']);

            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forwarding_rules');
    }
};