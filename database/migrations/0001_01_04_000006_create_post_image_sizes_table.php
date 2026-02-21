<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_image_sizes', function (Blueprint $table) {

            // id (AUTO_INCREMENT PRIMARY KEY)
            $table->id();

            // foreign key
            $table->unsignedBigInteger('post_image_id');

            // mezők
            $table->string('name');
            $table->string('file_uri')->unique();
            $table->string('file_url')->unique();

            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size');

            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // indexek
            $table->index('mime_type');
            $table->index('file_size');

            // foreign key constraint
            $table->foreign('post_image_id')
                ->references('id')
                ->on('post_images')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_image_sizes');
    }
};