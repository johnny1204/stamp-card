<?php

namespace App\Services;

use App\Models\Child;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * 子ども用署名付きURLサービス
 * 
 * 子どもがログインなしでアクセスできる署名付きURLを生成・管理
 */
class ChildUrlService
{
    /**
     * 子どものスタンプカード表示用署名付きURLを生成
     *
     * @param Child $child 対象の子ども
     * @param int $days 有効期間（日数）デフォルト30日
     * @return string 署名付きURL
     */
    public function generateStampCardsUrl(Child $child, int $days = 30): string
    {
        return URL::temporarySignedRoute(
            'child.stamp-cards',
            Carbon::now()->addDays($days),
            ['child' => $child->id]
        );
    }

    /**
     * 子どものスタンプ一覧表示用署名付きURLを生成
     *
     * @param Child $child 対象の子ども
     * @param int $days 有効期間（日数）デフォルト30日
     * @return string 署名付きURL
     */
    public function generateStampsUrl(Child $child, int $days = 30): string
    {
        return URL::temporarySignedRoute(
            'child.stamps',
            Carbon::now()->addDays($days),
            ['child' => $child->id]
        );
    }

    /**
     * 子どもの今日のスタンプ表示用署名付きURLを生成
     *
     * @param Child $child 対象の子ども
     * @param int $days 有効期間（日数）デフォルト30日
     * @return string 署名付きURL
     */
    public function generateTodayStampsUrl(Child $child, int $days = 30): string
    {
        return URL::temporarySignedRoute(
            'child.today-stamps',
            Carbon::now()->addDays($days),
            ['child' => $child->id]
        );
    }

    /**
     * 子ども用の全URLを生成
     *
     * @param Child $child 対象の子ども
     * @param int $days 有効期間（日数）デフォルト30日
     * @return array<string, string> URL配列
     */
    public function generateAllUrls(Child $child, int $days = 30): array
    {
        return [
            'stamp_cards' => $this->generateStampCardsUrl($child, $days),
            'stamps' => $this->generateStampsUrl($child, $days),
            'today_stamps' => $this->generateTodayStampsUrl($child, $days),
        ];
    }
}