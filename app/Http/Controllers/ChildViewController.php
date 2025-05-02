<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Stamp;
use App\Services\StampService;
use App\Services\StampCardService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

/**
 * 子ども用ビューコントローラー
 * 
 * 署名付きURLでアクセス可能な子ども専用画面を提供
 */
class ChildViewController extends Controller
{
    public function __construct(
        private StampService $stampService,
        private StampCardService $stampCardService
    ) {}

    /**
     * 子どものスタンプカード表示（署名付きURL用）
     *
     * @param Child $child 対象の子ども
     * @return Response
     */
    public function stampCards(Child $child): Response
    {
        // 既存データのマイグレーション（初回のみ実行）
        $this->stampCardService->migrateExistingData($child);
        
        $stampCards = $this->stampCardService->getChildStampCards($child);

        return Inertia::render('Child/StampCards', [
            'child' => $child,
            'stampCards' => $stampCards,
            'isChildView' => true, // 子ども専用表示フラグ
        ]);
    }

    /**
     * 子どものスタンプ一覧表示（署名付きURL用）
     *
     * @param Child $child 対象の子ども
     * @return Response
     */
    public function stamps(Child $child): Response
    {
        $stamps = $this->stampService->getChildStamps($child, 50); // 最新50件
        $statistics = $this->stampService->getStampStatistics($child);

        // 各スタンプの開封用署名付きURLを生成
        $stampOpenUrls = [];
        foreach ($stamps as $stamp) {
            $stampOpenUrls[$stamp->id] = \URL::signedRoute('child.stamps.open', [
                'child' => $child->id,
                'stamp' => $stamp->id
            ]);
        }

        return Inertia::render('Child/Stamps', [
            'child' => $child,
            'stamps' => $stamps,
            'statistics' => $statistics,
            'stampOpenUrls' => $stampOpenUrls,
            'isChildView' => true, // 子ども専用表示フラグ
        ]);
    }

    /**
     * 子どもの今日のスタンプ表示（署名付きURL用）
     *
     * @param Child $child 対象の子ども
     * @return Response
     */
    public function todayStamps(Child $child): Response
    {
        $todayStamps = $this->stampService->getTodayStamps($child);
        $todayCount = $todayStamps->count();
        $monthlyCount = $this->stampService->getThisMonthStamps($child)->count();

        // 各スタンプの開封用署名付きURLを生成
        $stampOpenUrls = [];
        foreach ($todayStamps as $stamp) {
            $stampOpenUrls[$stamp->id] = \URL::signedRoute('child.stamps.open', [
                'child' => $child->id,
                'stamp' => $stamp->id
            ]);
        }

        return Inertia::render('Child/TodayStamps', [
            'child' => $child,
            'todayStamps' => $todayStamps,
            'todayCount' => $todayCount,
            'monthlyCount' => $monthlyCount,
            'stampOpenUrls' => $stampOpenUrls,
            'isChildView' => true, // 子ども専用表示フラグ
        ]);
    }

    /**
     * スタンプを開封する（署名付きURL用API）
     *
     * @param Child $child 対象の子ども
     * @param Stamp $stamp 開封対象のスタンプ
     * @return JsonResponse
     */
    public function openStamp(Child $child, Stamp $stamp): JsonResponse
    {
        // スタンプが指定された子どものものか確認
        if ($stamp->child_id !== $child->id) {
            return response()->json([
                'success' => false,
                'message' => 'スタンプが見つかりません'
            ], 404);
        }

        try {
            $openedStamp = $this->stampService->openStamp($stamp);
            
            return response()->json([
                'success' => true,
                'stamp' => [
                    'id' => $openedStamp->id,
                    'opened_at' => $openedStamp->opened_at?->toISOString(),
                    'pokemon' => $openedStamp->pokemon ? [
                        'id' => $openedStamp->pokemon->id,
                        'name' => $openedStamp->pokemon->name,
                        'type1' => $openedStamp->pokemon->type1,
                        'type2' => $openedStamp->pokemon->type2,
                        'genus' => $openedStamp->pokemon->genus,
                        'is_legendary' => $openedStamp->pokemon->is_legendary,
                        'is_mythical' => $openedStamp->pokemon->is_mythical,
                    ] : null,
                    'stamp_type' => $openedStamp->stampType ? [
                        'id' => $openedStamp->stampType->id,
                        'name' => $openedStamp->stampType->name,
                        'icon' => $openedStamp->stampType->icon,
                        'color' => $openedStamp->stampType->color,
                    ] : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'スタンプの開封に失敗しました'
            ], 500);
        }
    }
}