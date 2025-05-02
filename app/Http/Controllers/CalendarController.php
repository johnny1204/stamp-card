<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarRequest;
use App\Models\Child;
use App\Services\CalendarService;
use App\Services\ChildService;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * カレンダー表示を担当するController
 */
class CalendarController extends Controller
{
    public function __construct(
        private CalendarService $calendarService,
        private ChildService $childService
    ) {}

    /**
     * 月間カレンダーページを表示
     *
     * @param Child $child 対象の子ども
     * @param CalendarRequest $request バリデーション済みリクエスト
     * @return Response Inertiaレスポンス
     */
    public function monthly(Child $child, CalendarRequest $request): Response
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $calendarData = $this->calendarService->getMonthlyCalendar($child, $year, $month);
        $children = $this->childService->getAllChildren();
        
        // 前月・次月のナビゲーション情報
        $currentDate = Carbon::create($year, $month, 1);
        $previousMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
        
        return Inertia::render('Calendar/Monthly', [
            'child' => $child,
            'children' => $children,
            'calendar_data' => $calendarData,
            'navigation' => [
                'current' => [
                    'year' => $year,
                    'month' => $month,
                    'month_name' => $currentDate->format('F'),
                ],
                'previous' => [
                    'year' => $previousMonth->year,
                    'month' => $previousMonth->month,
                    'month_name' => $previousMonth->format('F'),
                ],
                'next' => [
                    'year' => $nextMonth->year,
                    'month' => $nextMonth->month,
                    'month_name' => $nextMonth->format('F'),
                ],
            ],
        ]);
    }

    /**
     * 週間カレンダーページを表示
     *
     * @param Child $child 対象の子ども
     * @param CalendarRequest $request バリデーション済みリクエスト
     * @return Response Inertiaレスポンス
     */
    public function weekly(Child $child, CalendarRequest $request): Response
    {
        $dateString = $request->input('date', Carbon::now()->format('Y-m-d'));
        $startOfWeek = Carbon::parse($dateString)->startOfWeek();
        
        $calendarData = $this->calendarService->getWeeklyCalendar($child, $startOfWeek);
        $children = $this->childService->getAllChildren();
        
        // 前週・次週のナビゲーション情報
        $previousWeek = $startOfWeek->copy()->subWeek();
        $nextWeek = $startOfWeek->copy()->addWeek();
        
        return Inertia::render('Calendar/Weekly', [
            'child' => $child,
            'children' => $children,
            'calendar_data' => $calendarData,
            'navigation' => [
                'current_week_start' => $startOfWeek->format('Y-m-d'),
                'previous_week_start' => $previousWeek->format('Y-m-d'),
                'next_week_start' => $nextWeek->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * 日別詳細ページを表示
     *
     * @param Child $child 対象の子ども
     * @param CalendarRequest $request バリデーション済みリクエスト
     * @return Response Inertiaレスポンス
     */
    public function daily(Child $child, CalendarRequest $request): Response
    {
        $dateString = $request->input('date', Carbon::now()->format('Y-m-d'));
        $date = Carbon::parse($dateString);
        
        $dailyData = $this->calendarService->getDailyDetail($child, $date);
        $children = $this->childService->getAllChildren();
        
        // 前日・次日のナビゲーション情報
        $previousDay = $date->copy()->subDay();
        $nextDay = $date->copy()->addDay();
        
        return Inertia::render('Calendar/Daily', [
            'child' => $child,
            'children' => $children,
            'daily_data' => $dailyData,
            'navigation' => [
                'current_date' => $date->format('Y-m-d'),
                'previous_date' => $previousDay->format('Y-m-d'),
                'next_date' => $nextDay->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * カレンダーAPI - 月間データ
     *
     * @param Child $child 対象の子ども
     * @param CalendarRequest $request バリデーション済みリクエスト
     * @return \Illuminate\Http\JsonResponse JSONレスポンス
     */
    public function apiMonthly(Child $child, CalendarRequest $request): \Illuminate\Http\JsonResponse
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $calendarData = $this->calendarService->getMonthlyCalendar($child, $year, $month);
        
        return response()->json([
            'data' => $calendarData,
            'child' => $child,
        ]);
    }

    /**
     * カレンダーAPI - 週間データ
     *
     * @param Child $child 対象の子ども
     * @param CalendarRequest $request バリデーション済みリクエスト
     * @return \Illuminate\Http\JsonResponse JSONレスポンス
     */
    public function apiWeekly(Child $child, CalendarRequest $request): \Illuminate\Http\JsonResponse
    {
        $dateString = $request->input('date', Carbon::now()->format('Y-m-d'));
        $startOfWeek = Carbon::parse($dateString)->startOfWeek();
        
        $calendarData = $this->calendarService->getWeeklyCalendar($child, $startOfWeek);
        
        return response()->json([
            'data' => $calendarData,
            'child' => $child,
        ]);
    }

    /**
     * カレンダーAPI - 日別データ
     *
     * @param Child $child 対象の子ども
     * @param CalendarRequest $request バリデーション済みリクエスト
     * @return \Illuminate\Http\JsonResponse JSONレスポンス
     */
    public function apiDaily(Child $child, CalendarRequest $request): \Illuminate\Http\JsonResponse
    {
        $dateString = $request->input('date', Carbon::now()->format('Y-m-d'));
        $date = Carbon::parse($dateString);
        
        $dailyData = $this->calendarService->getDailyDetail($child, $date);
        
        return response()->json([
            'data' => $dailyData,
            'child' => $child,
        ]);
    }
}