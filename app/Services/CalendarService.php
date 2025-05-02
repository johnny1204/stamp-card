<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Stamp;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * カレンダー表示関連のビジネスロジックを担当するサービス
 */
class CalendarService extends BaseService
{
    /**
     * 指定した月のカレンダーデータを取得
     *
     * @param Child $child 対象の子ども
     * @param int $year 年
     * @param int $month 月
     * @return array<string, mixed> カレンダーデータ
     */
    public function getMonthlyCalendar(Child $child, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        
        // 指定月のスタンプを取得
        $stamps = $child->stamps()
            ->with(['pokemon', 'stampType'])
            ->whereBetween('stamped_at', [$startDate, $endDate])
            ->orderBy('stamped_at', 'asc')
            ->get();
        
        // 日付別にグループ化
        $stampsByDate = $stamps->groupBy(function ($stamp) {
            return Carbon::parse($stamp->stamped_at)->format('Y-m-d');
        });
        
        // カレンダー構造の生成
        $calendar = $this->buildCalendarStructure($year, $month, $stampsByDate);
        
        // 統計情報
        $statistics = $this->calculateMonthlyStatistics($stamps);
        
        return [
            'year' => $year,
            'month' => $month,
            'calendar' => $calendar,
            'statistics' => $statistics,
            'total_stamps' => $stamps->count(),
        ];
    }

    /**
     * 指定した週のカレンダーデータを取得
     *
     * @param Child $child 対象の子ども
     * @param Carbon $startOfWeek 週の開始日
     * @return array<string, mixed> 週間カレンダーデータ
     */
    public function getWeeklyCalendar(Child $child, Carbon $startOfWeek): array
    {
        $endOfWeek = $startOfWeek->copy()->endOfWeek();
        
        // 指定週のスタンプを取得
        $stamps = $child->stamps()
            ->with(['pokemon', 'stampType'])
            ->whereBetween('stamped_at', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
            ->orderBy('stamped_at', 'asc')
            ->get();
        
        // 日付別にグループ化
        $stampsByDate = $stamps->groupBy(function ($stamp) {
            return Carbon::parse($stamp->stamped_at)->format('Y-m-d');
        });
        
        // 週間カレンダー構造の生成
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateKey = $date->format('Y-m-d');
            $weekDays[] = [
                'date' => $dateKey,
                'day_of_week' => $date->dayOfWeek,
                'day_name' => $date->format('l'),
                'stamps' => $stampsByDate->get($dateKey, collect())->toArray(),
                'stamps_count' => $stampsByDate->get($dateKey, collect())->count(),
                'is_today' => $date->isToday(),
            ];
        }
        
        return [
            'start_of_week' => $startOfWeek->format('Y-m-d'),
            'end_of_week' => $endOfWeek->format('Y-m-d'),
            'week_days' => $weekDays,
            'total_stamps' => $stamps->count(),
        ];
    }

    /**
     * 指定した日のスタンプ詳細を取得
     *
     * @param Child $child 対象の子ども
     * @param Carbon $date 対象日
     * @return array<string, mixed> 日別詳細データ
     */
    public function getDailyDetail(Child $child, Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        $stamps = $child->stamps()
            ->with(['pokemon', 'stampType'])
            ->whereBetween('stamped_at', [$startOfDay, $endOfDay])
            ->orderBy('stamped_at', 'asc')
            ->get();
        
        // スタンプ種類別の集計
        $stampTypeStats = $stamps->groupBy('stamp_type_id')->map(function ($typeStamps) {
            $firstStamp = $typeStamps->first();
            return [
                'stamp_type' => [
                    'id' => $firstStamp->stampType->id,
                    'name' => $firstStamp->stampType->name,
                    'icon' => $firstStamp->stampType->icon,
                    'color' => $firstStamp->stampType->color,
                ],
                'count' => $typeStamps->count(),
                'stamps' => $typeStamps->toArray(),
            ];
        })->values();
        
        return [
            'date' => $date->format('Y-m-d'),
            'day_name' => $date->format('l'),
            'stamps' => $stamps->toArray(),
            'stamps_count' => $stamps->count(),
            'stamp_type_statistics' => $stampTypeStats->toArray(),
            'is_today' => $date->isToday(),
        ];
    }

    /**
     * 今月のカレンダーデータを取得
     *
     * @param Child $child 対象の子ども
     * @return array<string, mixed> 今月のカレンダーデータ
     */
    public function getCurrentMonthCalendar(Child $child): array
    {
        $now = Carbon::now();
        return $this->getMonthlyCalendar($child, $now->year, $now->month);
    }

    /**
     * 今週のカレンダーデータを取得
     *
     * @param Child $child 対象の子ども
     * @return array<string, mixed> 今週のカレンダーデータ
     */
    public function getCurrentWeekCalendar(Child $child): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        return $this->getWeeklyCalendar($child, $startOfWeek);
    }

    /**
     * カレンダー構造を構築（月間）
     *
     * @param int $year 年
     * @param int $month 月
     * @param \Illuminate\Database\Eloquent\Collection $stampsByDate 日付別スタンプ
     * @return array<array<string, mixed>> カレンダー構造
     */
    private function buildCalendarStructure(int $year, int $month, $stampsByDate): array
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // カレンダーの開始日（月の初日の週の始まり：日曜日開始）
        $calendarStart = $startDate->copy()->startOfWeek(Carbon::SUNDAY);
        
        // カレンダーの終了日（月の最終日の週の終わり：土曜日終了）
        $calendarEnd = $endDate->copy()->endOfWeek(Carbon::SATURDAY);
        
        $calendar = [];
        $current = $calendarStart->copy();
        
        while ($current <= $calendarEnd) {
            $dateKey = $current->format('Y-m-d');
            $dayStamps = $stampsByDate->get($dateKey, collect());
            
            $calendar[] = [
                'date' => $dateKey,
                'day' => $current->day,
                'day_of_week' => $current->dayOfWeek,
                'is_current_month' => $current->month === $month,
                'is_today' => $current->isToday(),
                'stamps' => $dayStamps->toArray(),
                'stamps_count' => $dayStamps->count(),
                'has_legendary' => $dayStamps->contains(fn($stamp) => $stamp->pokemon->is_legendary),
                'has_mythical' => $dayStamps->contains(fn($stamp) => $stamp->pokemon->is_mythical),
            ];
            
            $current->addDay();
        }
        
        // 週ごとにグループ化
        return array_chunk($calendar, 7);
    }

    /**
     * 月間統計を計算
     *
     * @param \Illuminate\Database\Eloquent\Collection $stamps スタンプコレクション
     * @return array<string, mixed> 統計情報
     */
    private function calculateMonthlyStatistics(Collection $stamps): array
    {
        $totalStamps = $stamps->count();
        $legendaryCount = $stamps->filter(fn($stamp) => $stamp->pokemon->is_legendary)->count();
        $mythicalCount = $stamps->filter(fn($stamp) => $stamp->pokemon->is_mythical)->count();
        
        // スタンプ種類別集計
        $stampTypeStats = $stamps->groupBy('stamp_type_id')->map(function ($typeStamps) {
            $firstStamp = $typeStamps->first();
            return [
                'stamp_type' => [
                    'id' => $firstStamp->stampType->id,
                    'name' => $firstStamp->stampType->name,
                    'icon' => $firstStamp->stampType->icon,
                    'color' => $firstStamp->stampType->color,
                ],
                'count' => $typeStamps->count(),
            ];
        })->values();
        
        // 日別スタンプ数
        $dailyStats = $stamps->groupBy(function ($stamp) {
            return Carbon::parse($stamp->stamped_at)->format('Y-m-d');
        })->map(function ($dayStamps) {
            return $dayStamps->count();
        });
        
        return [
            'total_stamps' => $totalStamps,
            'legendary_count' => $legendaryCount,
            'mythical_count' => $mythicalCount,
            'stamp_type_statistics' => $stampTypeStats->toArray(),
            'daily_statistics' => $dailyStats->toArray(),
            'average_per_day' => $totalStamps > 0 ? round($totalStamps / max(1, $dailyStats->count()), 1) : 0,
        ];
    }
}