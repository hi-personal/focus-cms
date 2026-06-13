<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;
use App\Models\PostImage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_image_relationships', function (Blueprint $table) {
            $table->foreignIdFor(Post::class)->constrained('posts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(PostImage::class)->constrained('post_images')->restrictOnUpdate()->restrictOnDelete();
            $table->integer('order')->default(0);

            $table->primary(['post_id', 'post_image_id']);

            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_image_relationships');
    }
};
