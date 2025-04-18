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
        Schema::table('stamps', function (Blueprint $table) {
            $table->foreignId('stamp_card_id')->nullable()->after('child_id')->constrained('stamp_cards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stamps', function (Blueprint $table) {
            $table->dropForeign(['stamp_card_id']);
            $table->dropColumn('stamp_card_id');
        });
    }
};