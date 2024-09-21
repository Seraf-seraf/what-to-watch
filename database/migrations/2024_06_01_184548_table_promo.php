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
        if (!Schema::hasTable('promo')) {
            Schema::create('promo', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('film_id');

                $table->foreign('film_id')
                    ->references('id')
                    ->on('films')
                    ->onUpdate('CASCADE')
                    ->onDelete('CASCADE');
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
