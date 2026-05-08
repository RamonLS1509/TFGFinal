<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('developer');
            $table->string('publisher');
            $table->decimal('price', 8, 2);
            $table->string('cover_image')->nullable();
            $table->json('screenshots')->nullable();
            $table->json('genres');               // ['Action', 'RPG']
            $table->json('platforms');             // ['Windows', 'Mac']
            $table->date('release_date');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('metacritic_score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
