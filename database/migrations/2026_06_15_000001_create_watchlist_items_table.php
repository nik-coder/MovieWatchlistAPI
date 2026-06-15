<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('imdb_id');
            $table->string('title');
            $table->string('status')->default('to_watch');
            $table->integer('rating')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('favorite')->default(false);
            $table->timestamp('watched_at')->nullable();
            $table->date('released_at')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('imdb_rating')->nullable();
            $table->string('type')->nullable();
            $table->string('year')->nullable();
            $table->string('runtime')->nullable();
            $table->string('genre')->nullable();
            $table->string('director')->nullable();
            $table->string('actors')->nullable();
            $table->text('plot')->nullable();
            $table->string('language')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'imdb_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist_items');
    }
};
