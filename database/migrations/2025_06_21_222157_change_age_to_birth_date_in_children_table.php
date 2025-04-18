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
        Schema::table('children', function (Blueprint $table) {
            // 既存のageカラムを削除
            $table->dropColumn('age');
            
            // birth_dateカラムを追加
            $table->date('birth_date')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            // birth_dateカラムを削除
            $table->dropColumn('birth_date');
            
            // ageカラムを復元
            $table->integer('age')->nullable()->after('name');
        });
    }
};