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
        
        Schema::connection($connection)->table('pokemons', function (Blueprint $table) {
            $table->string('type1', 50)->nullable()->after('name');
            $table->string('type2', 50)->nullable()->after('type1');
            $table->string('genus', 100)->nullable()->after('type2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = app()->environment('testing') ? 'sqlite' : 'mysql';
        
        Schema::connection($connection)->table('pokemons', function (Blueprint $table) {
            $table->dropColumn(['type1', 'type2', 'genus']);
        });
    }
};
