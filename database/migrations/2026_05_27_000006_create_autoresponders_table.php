<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autoresponders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mailbox_id')->unsigned();
            $table->string('subject', 255);
            $table->text('body');
            $table->boolean('is_html')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('interval_hours')->default(24)->comment('Min hours between auto-replies to same sender');
            $table->timestamps();

            $table->index(['mailbox_id']);
            $table->index(['status']);

            $table->foreign('mailbox_id')->references('id')->on('mailboxes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autoresponders');
    }
};