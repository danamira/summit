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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_attempt_id')
                ->references('id')->on('process_attempts')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->boolean('parsable');
            $table->string('type');
            $table->json('move_info')->nullable();
            $table->json('series_info')->nullable();
            $table->json('clip_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
