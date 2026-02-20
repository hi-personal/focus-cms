<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_image_albums', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Post::class)->constrained('posts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();

            $table->unique(['post_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_image_albums');
    }
};
