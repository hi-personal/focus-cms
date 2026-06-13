<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\PostType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('parent_id')->nullable()->constrained('posts', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('title');
            $table->string('name')->unique();
            $table->foreignIdFor(User::class)->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('post_type_name')->default('post');
            $table->longText('content')->nullable();
            $table->string('status', 100)->nullable();
            $table->timestamps(precision: 0);

            $table->index('parent_id');
            $table->index('status');
            $table->index('post_type_name');
            $table->index(['post_type_name', 'title'], 'posts_post_type_title_index'); // Kompozit index
            $table->index(['post_type_name', 'status'], 'posts_post_type_status_index'); // Kompozit index
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
