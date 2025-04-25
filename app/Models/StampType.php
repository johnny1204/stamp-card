<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * スタンプ種類マスタモデル（MySQL接続）
 * 
 * スタンプの種類情報をマスタデータとして管理
 */
class StampType extends Model
{
    use HasFactory;
    
    /** @var string データベース接続 */
    protected $connection = 'mysql';
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // テスト環境ではSQLiteを使用
        if (app()->environment('testing')) {
            $this->connection = 'sqlite';
        }
    }

    /** @var array<string> 代入可能な属性 */
    protected $fillable = [
        'name',
        'icon',
        'color',
        'category',
        'is_custom',
        'family_id',
        'is_system_default'
    ];

    /** @var array<string, string> 属性キャスト */
    protected $casts = [
        'is_custom' => 'boolean',
        'is_system_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * カテゴリに基づくスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory(\Illuminate\Database\Eloquent\Builder $query, string $category): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('category', $category);
    }

    /**
     * お手伝い系スタンプを取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHelp(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->byCategory('help');
    }

    /**
     * 生活習慣系スタンプを取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLifestyle(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->byCategory('lifestyle');
    }

    /**
     * 行動評価系スタンプを取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBehavior(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->byCategory('behavior');
    }

    /**
     * カスタムスタンプを取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCustom(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_custom', true);
    }

    /**
     * 標準スタンプを取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStandard(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_custom', false);
    }

    /**
     * システムデフォルトスタンプを取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystemDefault(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_system_default', true);
    }

    /**
     * 家族専用スタンプを取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $familyId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForFamily(\Illuminate\Database\Eloquent\Builder $query, int $familyId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function ($q) use ($familyId) {
            $q->where('family_id', $familyId)
              ->orWhere('is_system_default', true);
        });
    }
}
