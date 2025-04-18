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
        Schema::create('stamps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children');
            $table->bigInteger('stamp_type_id'); // MySQLテーブルへの参照なのでforeign制約なし
            $table->bigInteger('pokemon_id'); // MySQLテーブルへの参照なのでforeign制約なし
            $table->timestamp('stamped_at');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stamps');
    }
};
