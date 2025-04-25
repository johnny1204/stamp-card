<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * スタンプモデル（SQLite接続）
 * 
 * スタンプ記録テーブルとのマッピングを担当
 */
class Stamp extends Model
{
    use HasFactory;
    
    /** @var string データベース接続 */
    protected $connection = 'sqlite';

    /** @var array<string> 代入可能な属性 */
    protected $fillable = [
        'child_id',
        'stamp_type_id',
        'pokemon_id',
        'stamp_card_id',
        'stamped_at',
        'opened_at',
        'comment'
    ];

    /** @var array<string, string> 属性キャスト */
    protected $casts = [
        'child_id' => 'integer',
        'stamp_type_id' => 'integer',
        'pokemon_id' => 'integer',
        'stamp_card_id' => 'integer',
        'stamped_at' => 'datetime',
        'opened_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 関連する子どもを取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    /**
     * 関連するスタンプ種類を取得（MySQLから）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stampType(): BelongsTo
    {
        return $this->belongsTo(StampType::class, 'stamp_type_id', 'id');
    }

    /**
     * 関連するポケモンを取得（MySQLから）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pokemon(): BelongsTo
    {
        return $this->belongsTo(Pokemon::class, 'pokemon_id', 'id');
    }

    /**
     * 関連するスタンプカードを取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stampCard(): BelongsTo
    {
        return $this->belongsTo(StampCard::class);
    }

    /**
     * 今日作成されたスタンプのみを取得するスコープ
     * （自テーブル完結のスコープのみ許可）
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('stamped_at', today());
    }

    /**
     * 今月作成されたスタンプのみを取得するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('stamped_at', now()->month)
                     ->whereYear('stamped_at', now()->year);
    }

    /**
     * 指定された期間のスタンプを取得するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates(Builder $query, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate): Builder
    {
        return $query->whereBetween('stamped_at', [$startDate, $endDate]);
    }
}
