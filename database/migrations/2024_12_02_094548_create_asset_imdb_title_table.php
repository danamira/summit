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
        Schema::create('asset_imdb_title', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')
                ->references('id')->on('assets')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreignId('imdb_title_id')
                ->references('id')->on('imdb_titles')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_imdb_title');
    }
};
