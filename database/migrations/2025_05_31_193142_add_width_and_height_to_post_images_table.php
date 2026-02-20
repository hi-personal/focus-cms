<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // post_images táblához width és height mezők
        Schema::table('post_images', function (Blueprint $table) {
            $table->unsignedInteger('width')->nullable()->after('file_size');
            $table->unsignedInteger('height')->nullable()->after('width');
        });

        // post_image_sizes táblához width és height mezők
        Schema::table('post_image_sizes', function (Blueprint $table) {
            $table->unsignedInteger('width')->nullable()->after('file_size');
            $table->unsignedInteger('height')->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('post_images', function (Blueprint $table) {
            $table->dropColumn(['width', 'height']);
        });

        Schema::table('post_image_sizes', function (Blueprint $table) {
            $table->dropColumn(['width', 'height']);
        });
    }
};
