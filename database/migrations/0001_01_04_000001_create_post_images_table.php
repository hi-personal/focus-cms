<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('title');
            $table->string('file_uri', 255)->unique();
            $table->string('file_url', 255)->unique();
            $table->string('file_extension', 100)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->bigInteger('file_size');

            $table->index('mime_type');
            $table->index('file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};
