<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;
use App\Models\PostTerm;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_term_relationships', function (Blueprint $table) {
            $table->foreignIdFor(Post::class)->constrained('posts')->cascadeOnDelete();
            $table->foreignIdFor(PostTerm::class)->constrained('post_terms')->cascadeOnUpdate()->cascadeOnDelete();

            $table->primary(['post_id', 'post_term_id']);

            $table->index('post_term_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_term_relationships');
    }
};
