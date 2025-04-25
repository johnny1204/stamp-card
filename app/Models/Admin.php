<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

/**
 * 管理者モデル（SQLite接続）
 * 
 * 簡単なパスワード認証用
 */
class Admin extends Model
{
    use HasFactory;
    
    /** @var string データベース接続 */
    protected $connection = 'sqlite';

    /** @var array<string> 代入可能な属性 */
    protected $fillable = [
        'password_hash',
        'family_id'
    ];

    /** @var array<string, string> 属性キャスト */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array<string> 非表示属性 */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * パスワードを設定する
     *
     * @param string $password 平文パスワード
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Hash::make($password);
    }

    /**
     * パスワードを検証する
     *
     * @param string $password 平文パスワード
     * @return bool 検証結果
     */
    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password_hash);
    }

    /**
     * 管理者を作成または更新する
     *
     * @param string $password 新しいパスワード
     * @return static 管理者インスタンス
     */
    public static function createOrUpdate(string $password): static
    {
        $admin = static::first();
        
        if (!$admin) {
            $admin = new static();
        }
        
        $admin->setPassword($password);
        $admin->save();
        
        return $admin;
    }

    /**
     * 関連する家族を取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * 家族のスタンプ種類を取得
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFamilyStampTypes(): \Illuminate\Support\Collection
    {
        if (!$this->family_id) {
            // 家族が設定されていない場合はシステムデフォルトのみ
            return StampType::on('mysql')->where('is_system_default', true)->get();
        }

        return $this->family->stampTypes();
    }
}
