<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Services\PokemonSelectionService;
use App\Services\StampCardService;
use App\Services\ChildService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StampCardController extends Controller
{
    public function __construct(
        private PokemonSelectionService $pokemonSelectionService,
        private StampCardService $stampCardService,
        private ChildService $childService
    ) {}

    /**
     * 子どものスタンプカード一覧を表示
     *
     * @param Child $child
     * @return Response
     */
    public function index(Child $child): Response
    {
        // 既存データのマイグレーション（初回のみ実行）
        $this->stampCardService->migrateExistingData($child);
        
        $stampCards = $this->stampCardService->getChildStampCards($child);
        $children = $this->childService->getAllChildren();

        return Inertia::render('StampCards/Index', [
            'child' => $child,
            'children' => $children,
            'stampCards' => $stampCards,
        ]);
    }

    /**
     * 子どもの目標スタンプ数を更新
     * 新しいカード（スタンプ未押し）の場合のみ変更可能
     *
     * @param Request $request
     * @param Child $child
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTarget(Request $request, Child $child)
    {
        $request->validate([
            'target_stamps' => 'required|integer|min:1|max:50'
        ]);

        // 進行中のカードにスタンプが押されているかチェック
        $stamps = $child->stamps()->get();
        $totalStamps = $stamps->count();
        $currentTargetStamps = $child->target_stamps;
        $remainingStamps = $totalStamps % $currentTargetStamps;

        // 進行中のカードにスタンプがある場合は変更不可
        if ($remainingStamps > 0) {
            return back()->withErrors([
                'target_stamps' => '現在のカードにスタンプが押されているため、目標数を変更できません。'
            ]);
        }

        $child->update([
            'target_stamps' => $request->target_stamps
        ]);

        return back()->with('success', '目標スタンプ数を更新しました。');
    }
}
