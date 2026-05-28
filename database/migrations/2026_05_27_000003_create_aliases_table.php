<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aliases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('domain_id')->unsigned();
            $table->string('source', 255)->comment('Source email address');
            $table->text('destination')->comment('Destination email(s), comma separated');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_catchall')->default(false);
            $table->string('comment', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['domain_id']);
            $table->index(['source']);
            $table->index(['status']);

            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aliases');
    }
};