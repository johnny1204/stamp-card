<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ポケモンマスタモデル（MySQL接続）
 * 
 * マスタデータとして安定性・拡張性を重視してMySQLに保存
 */
class Pokemon extends Model
{
    use HasFactory;
    
    /** @var string テーブル名 */
    protected $table = 'pokemons';
    
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
        'type1',
        'type2',
        'genus',
        'is_legendary',
        'is_mythical'
    ];

    /** @var array<string, string> 属性キャスト */
    protected $casts = [
        'is_legendary' => 'boolean',
        'is_mythical' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 通常のポケモン（レジェンダリーでも幻でもない）を取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNormal(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_legendary', false)->where('is_mythical', false);
    }

    /**
     * レジェンダリーポケモンを取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLegendary(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_legendary', true);
    }

    /**
     * 幻のポケモンを取得
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMythical(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_mythical', true);
    }
}
