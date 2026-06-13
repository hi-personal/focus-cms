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
        Schema::table('users', function (Blueprint $table) {
            $table->string('login')->unique();
            $table->string('nicename')->unique();
            $table->string('display_name')->nullable();
            $table->string('status')->nullable();
            $table->string('role')->nullable();

            $table->index('login');
            $table->index('role');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'login',
                'nicename',
                'display_name',
                'status',
                'role'
            ]);
        });
    }
};
