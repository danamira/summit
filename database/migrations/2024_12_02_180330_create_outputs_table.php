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

            $table->string('asset_id')->nullable();
            $table->string('asset_type')->nullable();
            $table->string('asset_custom_id')->nullable();
            $table->string('asset_label')->nullable();


            $table->string('episode_title');
            $table->boolean('gpt_parsable');
            $table->string('gpt_type');

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
            $table->string('imdb_title')->nullable();
            $table->string('imdb_type')->nullable();
            $table->string('imdb_poster')->nullable();
            $table->integer('imdb_year')->nullable();
            $table->integer('imdb_year_end')->nullable();


            $table->boolean('imdb_title_exact_match')->default(false);
            $table->integer('imdb_title_edit_distance')->nullable();
            $table->boolean('imdb_type_match')->nullable();
            $table->boolean('imdb_year_provided')->default(false);
            $table->boolean('imdb_year_match')->nullable();


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
