<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

/**
 * 子どもモデル（SQLite接続）
 * 
 * 日々の記録データとして軽量・バックアップ容易なSQLiteに保存
 */
class Child extends Model
{
    use HasFactory;
    
    /** @var string データベース接続 */
    protected $connection = 'sqlite';

    /** @var array<string> 代入可能な属性 */
    protected $fillable = [
        'name',
        'birth_date',
        'avatar_path',
        'target_stamps'
    ];

    /** @var array<string, string> 属性キャスト */
    protected $casts = [
        'birth_date' => 'date',
        'target_stamps' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array<string> JSON出力時に含めるアクセサ */
    protected $appends = [
        'age',
        'age_group',
    ];

    /**
     * 現在の年齢を計算して取得
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function age(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->birth_date ? Carbon::parse($this->birth_date)->age : null,
        );
    }

    /**
     * 指定日での年齢を計算
     *
     * @param string|\Carbon\Carbon $date 基準日
     * @return int|null 年齢（誕生日が設定されていない場合はnull）
     */
    public function getAgeAtDate($date): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        $birthDate = Carbon::parse($this->birth_date);
        $targetDate = Carbon::parse($date);

        return $birthDate->diffInYears($targetDate);
    }

    /**
     * 年齢グループを取得（表示用）
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function ageGroup(): Attribute
    {
        return Attribute::make(
            get: function () {
                $age = $this->age;
                
                if ($age === null) {
                    return '未設定';
                }
                
                if ($age < 3) {
                    return '2歳以下';
                } elseif ($age <= 5) {
                    return '3-5歳';
                } elseif ($age <= 8) {
                    return '6-8歳';
                } elseif ($age <= 12) {
                    return '9-12歳';
                } else {
                    return '13歳以上';
                }
            }
        );
    }

    /**
     * 関連するスタンプを取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stamps(): HasMany
    {
        return $this->hasMany(Stamp::class);
    }

    /**
     * 関連する目標を取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * 今日のスタンプを取得するリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todayStamps(): HasMany
    {
        return $this->hasMany(Stamp::class)->whereDate('stamped_at', today());
    }

    /**
     * 今月のスタンプを取得するリレーション
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function thisMonthStamps(): HasMany
    {
        return $this->hasMany(Stamp::class)
                    ->whereMonth('stamped_at', now()->month)
                    ->whereYear('stamped_at', now()->year);
    }

}
