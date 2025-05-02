<?php

namespace App\Http\Controllers;

use App\Services\ChildService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private ChildService $childService
    ) {}

    /**
     * Display the home page.
     *
     * @return Response
     */
    public function index(): Response
    {
        $children = $this->childService->getAllChildren();
        
        return Inertia::render('Home', [
            'children' => $children,
        ]);
    }

    /**
     * スタンプカードページへの自動リダイレクト
     * 最初の子どものスタンプカードページへ直接遷移
     *
     * @return RedirectResponse
     */
    public function redirectToStampCards(): RedirectResponse
    {
        $children = $this->childService->getAllChildren();

        if ($children->isEmpty()) {
            return redirect()->route('children.create')
                ->with('info', 'まずお子様を登録してください');
        }

        // 常に最初の子どものページに遷移
        $child = $children->first();
        return redirect()->route('children.stamp-cards.index', $child);
    }

    /**
     * 今日のスタンプページへの自動リダイレクト
     * 最初の子どものスタンプページへ直接遷移
     *
     * @return RedirectResponse
     */
    public function redirectToTodayStamps(): RedirectResponse
    {
        $children = $this->childService->getAllChildren();

        if ($children->isEmpty()) {
            return redirect()->route('children.create')
                ->with('info', 'まずお子様を登録してください');
        }

        // 常に最初の子どものページに遷移
        $child = $children->first();
        return redirect()->route('children.stamps.index', $child);
    }

    /**
     * カレンダーページへの自動リダイレクト
     * 最初の子どものカレンダーページへ直接遷移
     *
     * @return RedirectResponse
     */
    public function redirectToCalendar(): RedirectResponse
    {
        $children = $this->childService->getAllChildren();

        if ($children->isEmpty()) {
            return redirect()->route('children.create')
                ->with('info', 'まずお子様を登録してください');
        }

        // 常に最初の子どものページに遷移
        $child = $children->first();
        return redirect()->route('children.calendar.monthly', $child);
    }

    /**
     * 統計ダッシュボードページへの自動リダイレクト
     * 最初の子どもの統計ページへ直接遷移
     *
     * @return RedirectResponse
     */
    public function redirectToStatistics(): RedirectResponse
    {
        $children = $this->childService->getAllChildren();

        if ($children->isEmpty()) {
            return redirect()->route('children.create')
                ->with('info', 'まずお子様を登録してください');
        }

        // 常に最初の子どものページに遷移
        $child = $children->first();
        return redirect()->route('children.statistics.dashboard', $child);
    }


    /**
     * 目標管理ページへの自動リダイレクト
     * 最初の子どもの目標ページへ直接遷移
     *
     * @return RedirectResponse
     */
    public function redirectToGoals(): RedirectResponse
    {
        $children = $this->childService->getAllChildren();

        if ($children->isEmpty()) {
            return redirect()->route('children.create')
                ->with('info', 'まずお子様を登録してください');
        }

        // 常に最初の子どものページに遷移
        $child = $children->first();
        return redirect()->route('children.goals.index', $child);
    }

    /**
     * レポートページへの自動リダイレクト
     * 最初の子どものレポートページへ直接遷移
     *
     * @return RedirectResponse
     */
    public function redirectToReports(): RedirectResponse
    {
        $children = $this->childService->getAllChildren();

        if ($children->isEmpty()) {
            return redirect()->route('children.create')
                ->with('info', 'まずお子様を登録してください');
        }

        // 常に最初の子どものページに遷移
        $child = $children->first();
        return redirect()->route('children.reports.dashboard', $child);
    }
}
