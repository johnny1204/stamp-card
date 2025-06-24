<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Child;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * QRコード生成サービス
 * 
 * 子ども用専用リンクのQRコードを生成する
 */
class QrCodeService
{
    /**
     * 子ども用スタンプ帳ページのQRコードを生成
     * 
     * @param Child $child 対象の子ども
     * @param string $type QRコードの種類 ('stamps' | 'stamp-cards' | 'today-stamps')
     * @param int $size QRコードサイズ（ピクセル）
     * @return string SVG形式のQRコード
     */
    public function generateChildQrCode(Child $child, string $type = 'stamps', int $size = 300): string
    {
        $url = $this->generateChildUrl($child, $type);
        
        $qrCode = QrCode::size($size)
            ->style('round')
            ->eye('circle')
            ->gradient(31, 81, 255, 255, 121, 198, 'diagonal')
            ->margin(2)
            ->errorCorrection('M')
            ->generate($url);
            
        return (string) $qrCode;
    }

    /**
     * 子ども用署名付きURLを生成
     * 
     * @param Child $child 対象の子ども
     * @param string $type ページタイプ
     * @return string 署名付きURL
     */
    public function generateChildUrl(Child $child, string $type = 'stamps'): string
    {
        $routeName = match ($type) {
            'stamp-cards' => 'child.stamp-cards',
            'today-stamps' => 'child.today-stamps',
            default => 'child.stamps',
        };

        return URL::signedRoute($routeName, ['child' => $child->id], now()->addDays(30));
    }

    /**
     * 複数のQRコードを一括生成
     * 
     * @param Child $child 対象の子ども
     * @return array<string, string> ['stamps' => 'svg_data', 'stamp-cards' => 'svg_data', ...]
     */
    public function generateAllQrCodes(Child $child): array
    {
        $types = ['stamps', 'stamp-cards', 'today-stamps'];
        $qrCodes = [];

        foreach ($types as $type) {
            $qrCodes[$type] = $this->generateChildQrCode($child, $type);
        }

        return $qrCodes;
    }

    /**
     * QRコード用のラベルを取得
     * 
     * @param string $type QRコードの種類
     * @return string 日本語ラベル
     */
    public function getQrCodeLabel(string $type): string
    {
        return match ($type) {
            'stamp-cards' => 'スタンプカード',
            'today-stamps' => '今日のスタンプ',
            default => 'スタンプ帳',
        };
    }
}