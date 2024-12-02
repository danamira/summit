<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outputs', function (Blueprint $table) {
            $table->id();
            $table->string('original');
            $table->boolean('parsable');
            $table->string('type');

            $table->string('movie_name')->nullable();
            $table->string('movie_year')->nullable();
            $table->string('movie_language')->nullable();

            $table->string('episode_name')->nullable();
            $table->string('episode_number')->nullable();
            $table->string('episode_language')->nullable();
            $table->string('episode_year')->nullable();

            $table->string('series_name')->nullable();
            $table->string('series_year')->nullable();
            $table->string('season_number')->nullable();

            $table->string('clip_language')->nullable();
            $table->string('clip_source')->nullable();
            $table->boolean('clip_is_trailer')->nullable();

            $table->string('imdb_id')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outputs');
    }
};
