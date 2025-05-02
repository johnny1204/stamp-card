<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatisticsRequest;
use App\Models\Child;
use App\Services\StatisticsService;
use App\Services\ChildService;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 統計表示を担当するController
 */
class StatisticsController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService,
        private ChildService $childService
    ) {}

    /**
     * 統計ダッシュボードページを表示
     *
     * @param Child $child 対象の子ども
     * @return Response Inertiaレスポンス
     */
    public function dashboard(Child $child): Response
    {
        $basicStats = $this->statisticsService->getBasicStatistics($child);
        $stampTypeStats = $this->statisticsService->getStampTypeStatistics($child);
        $pokemonStats = $this->statisticsService->getPokemonCollectionStatistics($child);
        $growthData = $this->statisticsService->getGrowthChartData($child, 30);
        $children = $this->childService->getAllChildren();

        return Inertia::render('Statistics/Dashboard', [
            'child' => $child,
            'children' => $children,
            'basic_statistics' => $basicStats,
            'stamp_type_statistics' => $stampTypeStats,
            'pokemon_statistics' => $pokemonStats,
            'growth_chart_data' => $growthData,
        ]);
    }

    /**
     * 詳細統計ページを表示
     *
     * @param Child $child 対象の子ども
     * @param StatisticsRequest $request バリデーション済みリクエスト
     * @return Response Inertiaレスポンス
     */
    public function detailed(Child $child, StatisticsRequest $request): Response
    {
        $period = $request->input('period', 'daily');
        $limit = $request->input('limit', 30);
        $days = $request->input('days', 30);

        $periodStats = $this->statisticsService->getPeriodStatistics($child, $period, $limit);
        $growthData = $this->statisticsService->getGrowthChartData($child, $days);
        $basicStats = $this->statisticsService->getBasicStatistics($child);
        $children = $this->childService->getAllChildren();

        return Inertia::render('Statistics/Detailed', [
            'child' => $child,
            'children' => $children,
            'period_statistics' => $periodStats,
            'growth_chart_data' => $growthData,
            'basic_statistics' => $basicStats,
            'filters' => [
                'period' => $period,
                'limit' => $limit,
                'days' => $days,
            ],
        ]);
    }

    /**
     * ポケモンコレクション統計ページを表示
     *
     * @param Child $child 対象の子ども
     * @return Response Inertiaレスポンス
     */
    public function pokemonCollection(Child $child): Response
    {
        $pokemonStats = $this->statisticsService->getPokemonCollectionStatistics($child);
        $basicStats = $this->statisticsService->getBasicStatistics($child);
        $children = $this->childService->getAllChildren();

        return Inertia::render('Statistics/PokemonCollection', [
            'child' => $child,
            'children' => $children,
            'pokemon_statistics' => $pokemonStats,
            'basic_statistics' => $basicStats,
        ]);
    }

    /**
     * 月間レポートページを表示
     *
     * @param Child $child 対象の子ども
     * @param StatisticsRequest $request バリデーション済みリクエスト
     * @return Response Inertiaレスポンス
     */
    public function monthlyReport(Child $child, StatisticsRequest $request): Response
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $reportData = $this->statisticsService->generateMonthlyReport($child, $year, $month);
        $children = $this->childService->getAllChildren();

        // 前月・次月のナビゲーション情報
        $currentDate = Carbon::create($year, $month, 1);
        $previousMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        return Inertia::render('Statistics/MonthlyReport', [
            'child' => $child,
            'children' => $children,
            'report_data' => $reportData,
            'navigation' => [
                'current' => [
                    'year' => $year,
                    'month' => $month,
                    'month_name' => $currentDate->format('Y年n月'),
                ],
                'previous' => [
                    'year' => $previousMonth->year,
                    'month' => $previousMonth->month,
                    'month_name' => $previousMonth->format('Y年n月'),
                ],
                'next' => [
                    'year' => $nextMonth->year,
                    'month' => $nextMonth->month,
                    'month_name' => $nextMonth->format('Y年n月'),
                ],
            ],
        ]);
    }

    /**
     * 統計API - 基本統計
     *
     * @param Child $child 対象の子ども
     * @return \Illuminate\Http\JsonResponse JSONレスポンス
     */
    public function apiBasicStatistics(Child $child): \Illuminate\Http\JsonResponse
    {
        $basicStats = $this->statisticsService->getBasicStatistics($child);

        return response()->json([
            'data' => $basicStats,
            'child' => $child,
        ]);
    }

    /**
     * 統計API - 期間別統計
     *
     * @param Child $child 対象の子ども
     * @param StatisticsRequest $request バリデーション済みリクエスト
     * @return \Illuminate\Http\JsonResponse JSONレスポンス
     */
    public function apiPeriodStatistics(Child $child, StatisticsRequest $request): \Illuminate\Http\JsonResponse
    {
        $period = $request->input('period', 'daily');
        $limit = $request->input('limit', 30);

        $periodStats = $this->statisticsService->getPeriodStatistics($child, $period, $limit);

        return response()->json([
            'data' => $periodStats,
            'child' => $child,
        ]);
    }

    /**
     * 統計API - スタンプ種類別統計
     *
     * @param Child $child 対象の子ども
     * @param StatisticsRequest $request バリデーション済みリクエスト
     * @return \Illuminate\Http\JsonResponse JSONレスポンス
     */
    public function apiStampTypeStatistics(Child $child, StatisticsRequest $request): \Illuminate\Http\JsonResponse
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : null;

        $stampTypeStats = $this->statisticsService->getStampTypeStatistics($child, $startDate, $endDate);

        return response()->json([
            'data' => $stampTypeStats,
            'child' => $child,
            'period' => [
                'start_date' => $startDate?->format('Y-m-d'),
                'end_date' => $endDate?->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * 統計API - ポケモンコレクション統計
     *
     * @param Child $child 対象の子ども
     * @return \Illuminate\Http\JsonResponse JSONレスポンス
     */
    public function apiPokemonStatistics(Child $child): \Illuminate\Http\JsonResponse
    {
        $pokemonStats = $this->statisticsService->getPokemonCollectionStatistics($child);

        return response()->json([
            'data' => $pokemonStats,
            'child' => $child,
        ]);
    }

    /**
     * 統計API - 成長グラフデータ
     *
     * @param Child $child 対象の子ども
     * @param StatisticsRequest $request バリデーション済みリクエスト
     * @return \Illuminate\Http\JsonResponse JSONレスポンス
     */
    public function apiGrowthChartData(Child $child, StatisticsRequest $request): \Illuminate\Http\JsonResponse
    {
        $days = $request->input('days', 30);

        $growthData = $this->statisticsService->getGrowthChartData($child, $days);

        return response()->json([
            'data' => $growthData,
            'child' => $child,
        ]);
    }

    /**
     * 統計API - 月間レポート
     *
     * @param Child $child 対象の子ども
     * @param StatisticsRequest $request バリデーション済みリクエスト
     * @return \Illuminate\Http\JsonResponse JSONレスポンス
     */
    public function apiMonthlyReport(Child $child, StatisticsRequest $request): \Illuminate\Http\JsonResponse
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $reportData = $this->statisticsService->generateMonthlyReport($child, $year, $month);

        return response()->json([
            'data' => $reportData,
            'child' => $child,
        ]);
    }
}