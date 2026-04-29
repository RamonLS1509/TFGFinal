<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('score');  // 1-10
            $table->string('title', 150);
            $table->text('body');
            $table->boolean('recommended')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'game_id']); // una reseña por usuario por juego
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
