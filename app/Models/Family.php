<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 家族モデル
 *
 * 家族グループの管理を担当
 */
class Family extends Model
{
    use HasFactory;

    /** @var string データベース接続 */
    protected $connection = 'sqlite';

    /** @var array<string> 代入可能な属性 */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * 家族に属する管理者を取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }

    /**
     * 家族に属する子どもを取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    /**
     * 家族のスタンプ種類を取得（MySQLから）
     *
     * @return \Illuminate\Support\Collection
     */
    public function stampTypes(): \Illuminate\Support\Collection
    {
        return StampType::on('mysql')
            ->where(function ($query) {
                $query->where('family_id', $this->id)
                      ->orWhere('is_system_default', true);
            })
            ->orderBy('is_system_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * 家族専用のスタンプ種類のみを取得
     *
     * @return \Illuminate\Support\Collection
     */
    public function customStampTypes(): \Illuminate\Support\Collection
    {
        return StampType::on('mysql')
            ->where('family_id', $this->id)
            ->orderBy('name')
            ->get();
    }
}
