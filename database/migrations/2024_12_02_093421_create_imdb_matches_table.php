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
        Schema::create('imdb_matches', function (Blueprint $table) {
            $table->id();


            $table->foreignId('imdb_match_attempt_id')
                ->references('id')->on('imdb_match_attempts')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreignId('imdb_title_id')
                ->references('id')->on('imdb_titles')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->boolean('year_match')->nullable();

            $table->boolean('type_match')->nullable();

            $table->integer('levenshtein');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imdb_matches');
    }
};
