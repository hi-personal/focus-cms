<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_metas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(User::class)->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->longText('value')->nullable();
            $table->boolean('transient')->default(false);
            $table->dateTime('valid')->nullable();

            $table->unique(['user_id', 'name']);

            $table->index(['user_id', 'name'], 'user_metas_user_id_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_metas');
    }
};
