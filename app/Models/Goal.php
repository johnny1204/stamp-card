<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * 目標モデル
 *
 * 子どもの目標設定と達成状況を管理するモデル
 */
class Goal extends Model
{
    use HasFactory;

    /** @var string データベース接続 */
    protected $connection = 'sqlite';

    /** @var array<string> 代入可能な属性 */
    protected $fillable = [
        'child_id',
        'stamp_type_id',
        'target_count',
        'period_type',
        'start_date',
        'end_date',
        'reward_text',
        'is_achieved',
        'achieved_at',
    ];

    /** @var array<string, string> キャスト設定 */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'achieved_at' => 'datetime',
        'is_achieved' => 'boolean',
        'target_count' => 'integer',
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
     * 関連するスタンプタイプを取得（マスタDB）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stampType(): BelongsTo
    {
        return $this->belongsTo(StampType::class, 'stamp_type_id', 'id');
    }

    /**
     * 現在アクティブな目標を取得するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->where('is_achieved', false);
    }

    /**
     * 達成済みの目標を取得するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAchieved($query)
    {
        return $query->where('is_achieved', true);
    }

    /**
     * 期間別の目標を取得するスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $periodType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPeriodType($query, string $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * 進捗率を計算するアクセサ
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if ($this->target_count <= 0) {
                    return 0;
                }
                
                $currentCount = $this->getCurrentCount();
                return min(100, (int) round(($currentCount / $this->target_count) * 100));
            }
        );
    }

    /**
     * 現在のスタンプ数を取得するアクセサ
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function currentCount(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->getCurrentCount()
        );
    }

    /**
     * 残り日数を取得するアクセサ
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function remainingDays(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                $today = now()->startOfDay();
                $endDate = $this->end_date->startOfDay();
                
                if ($endDate < $today) {
                    return 0;
                }
                
                return $today->diffInDays($endDate) + 1;
            }
        );
    }

    /**
     * 現在のスタンプ数を取得
     *
     * @return int
     */
    private function getCurrentCount(): int
    {
        return Stamp::where('child_id', $this->child_id)
                   ->where('stamp_type_id', $this->stamp_type_id)
                   ->whereBetween('stamped_at', [$this->start_date, $this->end_date->endOfDay()])
                   ->count();
    }

    /**
     * 目標達成をチェックして更新
     *
     * @return bool 達成したかどうか
     */
    public function checkAndUpdateAchievement(): bool
    {
        if ($this->is_achieved) {
            return true;
        }

        $currentCount = $this->getCurrentCount();
        
        if ($currentCount >= $this->target_count) {
            $this->update([
                'is_achieved' => true,
                'achieved_at' => now(),
            ]);
            
            return true;
        }

        return false;
    }
}
