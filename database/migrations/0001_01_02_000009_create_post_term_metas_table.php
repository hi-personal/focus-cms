<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PostTerm;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_term_metas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(PostTerm::class)->constrained('post_terms')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->longText('value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_term_metas');
    }
};
