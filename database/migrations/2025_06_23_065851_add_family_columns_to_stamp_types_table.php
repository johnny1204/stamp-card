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
        Schema::connection('mysql')->table('stamp_types', function (Blueprint $table) {
            $table->unsignedBigInteger('family_id')->nullable()->after('id');
            $table->boolean('is_system_default')->default(false)->after('is_custom');
            
            $table->index('family_id');
            $table->index('is_system_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->table('stamp_types', function (Blueprint $table) {
            $table->dropIndex(['family_id']);
            $table->dropIndex(['is_system_default']);
            $table->dropColumn(['family_id', 'is_system_default']);
        });
    }
};
