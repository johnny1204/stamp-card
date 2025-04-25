<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * スタンプカードモデル（SQLite接続）
 */
class StampCard extends Model
{
    use HasFactory;
    
    /** @var string データベース接続 */
    protected $connection = 'sqlite';

    /** @var array<string> 代入可能な属性 */
    protected $fillable = [
        'child_id',
        'card_number',
        'target_stamps',
        'is_completed',
        'completed_at',
    ];

    /** @var array<string, string> 属性キャスト */
    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array<string> JSON出力時に含めるアクセサ */
    protected $appends = [
        'progress',
    ];

    /**
     * 関連する子どもを取得
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    /**
     * 関連するスタンプを取得
     */
    public function stamps(): HasMany
    {
        return $this->hasMany(Stamp::class);
    }

    /**
     * カードの進捗を取得
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function progress(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stamps()->count(),
        );
    }

    /**
     * カードが完成可能かチェック
     */
    public function canComplete(): bool
    {
        return !$this->is_completed && $this->progress >= $this->target_stamps;
    }

    /**
     * カードを完成させる
     */
    public function complete(): void
    {
        if ($this->canComplete()) {
            $this->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }
    }
}