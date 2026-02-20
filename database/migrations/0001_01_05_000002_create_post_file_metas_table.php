<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PostFile;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_file_metas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(PostFile::class)->constrained('post_files')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->longText('value')->nullable();
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_file_metas');
    }
};
