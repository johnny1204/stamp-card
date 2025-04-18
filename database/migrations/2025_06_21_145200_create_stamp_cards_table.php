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
        Schema::create('stamp_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained('children')->onDelete('cascade');
            $table->integer('card_number'); // カード番号（1, 2, 3...）
            $table->integer('target_stamps'); // このカードの目標スタンプ数
            $table->boolean('is_completed')->default(false); // カード完成フラグ
            $table->timestamp('completed_at')->nullable(); // 完成日時
            $table->timestamps();
            
            $table->unique(['child_id', 'card_number']); // 子どもごとにカード番号はユニーク
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stamp_cards');
    }
};