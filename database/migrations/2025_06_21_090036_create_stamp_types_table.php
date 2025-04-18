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
        
        Schema::connection($connection)->create('stamp_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('icon', 100)->nullable();
            $table->string('color', 7)->nullable();
            $table->string('category'); // テスト環境ではenum使わずstring
            $table->boolean('is_custom')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stamp_types');
    }
};
