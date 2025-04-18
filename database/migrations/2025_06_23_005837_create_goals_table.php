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
        Schema::connection('sqlite')->create('goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('child_id');
            $table->unsignedBigInteger('stamp_type_id'); // マスタDBのstamp_types.idを参照
            $table->integer('target_count');
            $table->enum('period_type', ['weekly', 'monthly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reward_text')->nullable();
            $table->boolean('is_achieved')->default(false);
            $table->timestamp('achieved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('child_id')->references('id')->on('children')->onDelete('cascade');
            $table->index(['child_id', 'period_type']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
