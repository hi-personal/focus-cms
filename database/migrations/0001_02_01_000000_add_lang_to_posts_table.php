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
        Schema::table('posts', function (Blueprint $table) {

            $table->string('lang', 255)
                ->nullable()
                ->after('parent_id');

            $table->unsignedBigInteger('lang_parent_id')
                ->nullable()
                ->after('lang');

            $table->foreign('lang_parent_id')
                ->references('id')
                ->on('posts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('lang');
            $table->index('lang_parent_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {

            $table->dropForeign(['lang_parent_id']);
            $table->dropIndex(['lang']);
            $table->dropIndex(['lang_parent_id']);

            $table->dropColumn('lang_parent_id');
            $table->dropColumn('lang');

        });
    }
};