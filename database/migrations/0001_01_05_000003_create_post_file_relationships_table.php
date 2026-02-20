<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;
use App\Models\PostFile;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_file_relationships', function (Blueprint $table) {
            $table->foreignIdFor(Post::class)->constrained('posts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(PostFile::class)->constrained('post_files')->restrictOnUpdate()->restrictOnUpdate();
            $table->integer('order')->default(0);

            $table->primary(['post_id', 'post_file_id']);
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_file_relationships');
    }
};
