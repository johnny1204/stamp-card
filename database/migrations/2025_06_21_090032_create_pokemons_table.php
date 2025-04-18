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
        $connection = app()->environment('testing') ? 'sqlite' : 'mysql';
        
        Schema::connection($connection)->create('pokemons', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name', 100);
            $table->boolean('is_legendary')->default(false);
            $table->boolean('is_mythical')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pokemons');
    }
};
