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
        Schema::create('imdb_match_attempts', function (Blueprint $table) {
            $table->id();

            $table->string('query')->nullable();


            $table->foreignId('asset_id')
                ->references('id')->on('assets')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->boolean('initiated')->default(false);
            $table->boolean('successful')->default(false);

            $table->longText('error')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imdb_match_attempts');
    }
};
