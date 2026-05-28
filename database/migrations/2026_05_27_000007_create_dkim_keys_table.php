<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dkim_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('domain_id')->unsigned();
            $table->string('selector', 63);
            $table->text('private_key');
            $table->text('public_key');
            $table->text('dns_record')->nullable();
            $table->integer('key_bits')->default(2048);
            $table->enum('status', ['active', 'inactive', 'rotating'])->default('active');
            $table->timestamps();

            $table->unique(['domain_id', 'selector']);

            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dkim_keys');
    }
};