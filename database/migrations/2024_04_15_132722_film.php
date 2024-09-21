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
        if (!Schema::hasTable('films')) {
            Schema::create('films', function (Blueprint $table) {
                $table->id();
                $table->string('name', 64)->nullable();
                $table->string('posterImage', 255)->nullable();
                $table->string('previewImage', 255)->nullable();
                $table->string('backgroundImage', 255)->nullable();
                $table->string('backgroundColor', 64)->nullable();
                $table->string('videoLink', 255)->nullable();
                $table->string('previewVideoLink', 255)->nullable();
                $table->string('description', 255)->nullable();
                $table->string('director', 255)->nullable();
                $table->json('starring')->nullable();
                $table->integer('runTime')->nullable();
                $table->json('genre')->nullable();
                $table->integer('released')->nullable();
                $table->string('status')->default('pending');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
