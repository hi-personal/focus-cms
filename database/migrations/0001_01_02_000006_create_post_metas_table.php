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
        Schema::create('post_metas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Post::class)->constrained('posts')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->longText('value')->nullable();

            $table->index(['post_id', 'name'], 'post_metas_post_id_name_index');
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_metas');
    }
};
