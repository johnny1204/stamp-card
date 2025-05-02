<?php

namespace App\Services;

use App\Models\Stamp;
use App\Models\Child;
use App\Models\StampType;
use App\Models\Pokemon;
use App\Models\Goal;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

/**
 * レポート生成サービス
 *
 * 詳細な統計レポートの生成を担当
 */
class ReportService
{
    /**
     * 子どもの詳細レポートを生成
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options レポートオプション
     * @return array<string, mixed> レポートデータ
     */
    public function generateDetailedReport(int $childId, array $options = []): array
    {
        $child = Child::findOrFail($childId);
        
        // デフォルトオプション
        $defaultOptions = [
            'start_date' => now()->subMonths(3)->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'include_pokemon' => true,
            'include_trends' => true,
            'include_achievements' => true,
            'group_by' => 'month', // day, week, month
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        return [
            'child' => $child,
            'period' => [
                'start_date' => $options['start_date'],
                'end_date' => $options['end_date'],
            ],
            'summary' => $this->generateSummary($childId, $options),
            'stamps_by_type' => $this->getStampsByType($childId, $options),
            'activity_trend' => $this->getActivityTrend($childId, $options),
            'pokemon_collection' => $options['include_pokemon'] ? $this->getPokemonCollectionStats($childId, $options) : null,
            'achievements' => $options['include_achievements'] ? $this->getAchievementStats($childId, $options) : null,
            'daily_patterns' => $this->getDailyPatterns($childId, $options),
            'monthly_comparison' => $this->getMonthlyComparison($childId, $options),
        ];
    }

    /**
     * 月間レポートを自動生成
     *
     * @param int $childId 子どもID
     * @param Carbon|null $month 対象月（未指定の場合は今月）
     * @return array<string, mixed> 月間レポートデータ
     */
    public function generateMonthlyReport(int $childId, ?Carbon $month = null): array
    {
        $month = $month ?? now();
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();
        
        $options = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'group_by' => 'day',
        ];
        
        $report = $this->generateDetailedReport($childId, $options);
        
        // 月間レポート特有の期間情報を追加
        $report['period'] = [
            'month' => $month->format('Y-m'),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];
        
        // 月間特有の分析を追加
        $report['monthly_highlights'] = $this->getMonthlyHighlights($childId, $startDate, $endDate);
        $report['goals_progress'] = $this->getGoalsProgressForPeriod($childId, $startDate, $endDate);
        
        return $report;
    }

    /**
     * 概要統計を生成
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options オプション
     * @return array<string, mixed> 概要統計
     */
    private function generateSummary(int $childId, array $options): array
    {
        $stamps = Stamp::where('child_id', $childId)
                      ->whereBetween('stamped_at', [$options['start_date'], $options['end_date']])
                      ->get();
        
        $totalDays = $options['start_date']->diffInDays($options['end_date']) + 1;
        $activeDays = $stamps->groupBy(fn($stamp) => Carbon::parse($stamp->stamped_at)->format('Y-m-d'))->count();
        
        return [
            'total_stamps' => $stamps->count(),
            'total_days' => $totalDays,
            'active_days' => $activeDays,
            'activity_rate' => $totalDays > 0 ? round(($activeDays / $totalDays) * 100, 1) : 0,
            'average_per_day' => $activeDays > 0 ? round($stamps->count() / $activeDays, 1) : 0,
            'unique_stamp_types' => $stamps->pluck('stamp_type_id')->unique()->count(),
            'opened_stamps' => $stamps->whereNotNull('opened_at')->count(),
            'unopened_stamps' => $stamps->whereNull('opened_at')->count(),
        ];
    }

    /**
     * スタンプタイプ別の統計を取得
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options オプション
     * @return Collection スタンプタイプ別統計
     */
    private function getStampsByType(int $childId, array $options): Collection
    {
        return Stamp::where('child_id', $childId)
                   ->whereBetween('stamped_at', [$options['start_date'], $options['end_date']])
                   ->join('stamp_types', 'stamps.stamp_type_id', '=', 'stamp_types.id')
                   ->select([
                       'stamp_types.id',
                       'stamp_types.name',
                       'stamp_types.icon',
                       'stamp_types.color',
                       DB::raw('COUNT(*) as count'),
                       DB::raw('AVG(CASE WHEN stamps.opened_at IS NOT NULL THEN 1 ELSE 0 END) * 100 as open_rate')
                   ])
                   ->groupBy('stamp_types.id', 'stamp_types.name', 'stamp_types.icon', 'stamp_types.color')
                   ->orderBy('count', 'desc')
                   ->get();
    }

    /**
     * 活動傾向を取得
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options オプション
     * @return Collection 活動傾向データ
     */
    private function getActivityTrend(int $childId, array $options): Collection
    {
        $groupBy = $options['group_by'];
        
        // データベース非依存でデータを取得
        $stamps = Stamp::where('child_id', $childId)
                      ->whereBetween('stamped_at', [$options['start_date'], $options['end_date']])
                      ->with('stampType')
                      ->get();
        
        // PHPで日付グループ化を実行
        $grouped = $stamps->groupBy(function ($stamp) use ($groupBy) {
            return $this->formatDateForGrouping($stamp->stamped_at, $groupBy);
        })->map(function ($group) {
            return [
                'count' => $group->count(),
                'unique_types' => $group->pluck('stamp_type_id')->unique()->count(),
            ];
        });
        
        // Collectionの形式に変換
        $result = $grouped->map(function ($data, $period) {
            return (object) [
                'period' => $period,
                'count' => $data['count'],
                'unique_types' => $data['unique_types'],
            ];
        })->sortBy('period')->values();
        
        // 欠損期間を0で補完
        return $this->fillMissingPeriods($result, $options, $groupBy);
    }

    /**
     * ポケモンコレクション統計を取得
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options オプション
     * @return array<string, mixed> ポケモン統計
     */
    private function getPokemonCollectionStats(int $childId, array $options): array
    {
        $stamps = Stamp::where('child_id', $childId)
                      ->whereBetween('stamped_at', [$options['start_date'], $options['end_date']])
                      ->whereNotNull('pokemon_id')
                      ->with('pokemon')
                      ->get();
        
        $pokemonCounts = $stamps->groupBy('pokemon_id')
                               ->map(fn($group) => $group->count())
                               ->sortDesc();
        
        $legendaryCount = $stamps->filter(fn($stamp) => $stamp->pokemon?->is_legendary)->count();
        $mythicalCount = $stamps->filter(fn($stamp) => $stamp->pokemon?->is_mythical)->count();
        
        return [
            'total_pokemon' => $stamps->pluck('pokemon_id')->unique()->count(),
            'total_collected' => $stamps->count(),
            'legendary_count' => $legendaryCount,
            'mythical_count' => $mythicalCount,
            'rare_rate' => $stamps->count() > 0 ? round((($legendaryCount + $mythicalCount) / $stamps->count()) * 100, 1) : 0,
            'most_collected' => $pokemonCounts->take(5),
            'recent_rare' => $stamps->filter(fn($stamp) => $stamp->pokemon?->is_legendary || $stamp->pokemon?->is_mythical)
                                   ->sortByDesc('stamped_at')
                                   ->take(3)
                                   ->values(),
        ];
    }

    /**
     * 達成統計を取得
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options オプション
     * @return array<string, mixed> 達成統計
     */
    private function getAchievementStats(int $childId, array $options): array
    {
        // 完成したスタンプカードの数を取得
        $completedCards = DB::connection('sqlite')
                           ->table('stamp_cards')
                           ->where('child_id', $childId)
                           ->where('is_completed', true)
                           ->whereBetween('completed_at', [$options['start_date'], $options['end_date']])
                           ->count();
        
        return [
            'completed_cards' => $completedCards,
            'achievement_rate' => 0, // 目標達成率（後で実装）
            'best_streak' => $this->calculateBestStreak($childId, $options),
            'consistency_score' => $this->calculateConsistencyScore($childId, $options),
        ];
    }

    /**
     * 日別パターン分析を取得
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options オプション
     * @return array<string, mixed> 日別パターン
     */
    private function getDailyPatterns(int $childId, array $options): array
    {
        $stamps = Stamp::where('child_id', $childId)
                      ->whereBetween('stamped_at', [$options['start_date'], $options['end_date']])
                      ->get();
        
        $dayOfWeek = $stamps->groupBy(fn($stamp) => Carbon::parse($stamp->stamped_at)->dayOfWeek)
                           ->map(fn($group) => $group->count())
                           ->sortKeys();
        
        $hourOfDay = $stamps->groupBy(fn($stamp) => Carbon::parse($stamp->stamped_at)->hour)
                           ->map(fn($group) => $group->count())
                           ->sortKeys();
        
        return [
            'by_day_of_week' => $dayOfWeek,
            'by_hour_of_day' => $hourOfDay,
            'most_active_day' => $dayOfWeek->keys()->first(),
            'most_active_hour' => $hourOfDay->keys()->first(),
        ];
    }

    /**
     * 月間比較データを取得
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options オプション
     * @return Collection 月間比較データ
     */
    private function getMonthlyComparison(int $childId, array $options): Collection
    {
        $months = 6; // 過去6ヶ月分
        $startDate = now()->subMonths($months)->startOfMonth();
        
        // データベース非依存でデータを取得
        $stamps = Stamp::where('child_id', $childId)
                      ->where('stamped_at', '>=', $startDate)
                      ->get();
        
        // PHPで月別グループ化を実行
        return $stamps->groupBy(function ($stamp) {
            return $stamp->stamped_at->format('Y-m');
        })->map(function ($group, $month) {
            return (object) [
                'month' => $month,
                'count' => $group->count(),
                'unique_types' => $group->pluck('stamp_type_id')->unique()->count(),
                'active_days' => $group->pluck('stamped_at')
                                     ->map(fn($date) => $date->format('Y-m-d'))
                                     ->unique()
                                     ->count(),
            ];
        })->sortBy('month')
          ->values();
    }

    /**
     * 月間ハイライトを取得
     *
     * @param int $childId 子どもID
     * @param Carbon $startDate 開始日
     * @param Carbon $endDate 終了日
     * @return array<string, mixed> 月間ハイライト
     */
    private function getMonthlyHighlights(int $childId, Carbon $startDate, Carbon $endDate): array
    {
        $stamps = Stamp::where('child_id', $childId)
                      ->whereBetween('stamped_at', [$startDate, $endDate])
                      ->get();
        
        $dailyCounts = $stamps->groupBy(fn($stamp) => Carbon::parse($stamp->stamped_at)->format('Y-m-d'))
                             ->map(fn($group) => $group->count());
        
        return [
            'best_day' => [
                'date' => $dailyCounts->keys()->first(),
                'count' => $dailyCounts->max(),
            ],
            'total_stamps' => $stamps->count(),
            'new_pokemon' => $stamps->pluck('pokemon_id')->unique()->count(),
            'perfect_days' => $dailyCounts->filter(fn($count) => $count >= 3)->count(), // 3個以上を完璧な日とする
        ];
    }

    /**
     * 期間内の目標進捗を取得
     *
     * @param int $childId 子どもID
     * @param Carbon $startDate 開始日
     * @param Carbon $endDate 終了日
     * @return Collection 目標進捗
     */
    private function getGoalsProgressForPeriod(int $childId, Carbon $startDate, Carbon $endDate): Collection
    {
        // 後で目標機能が完成したら実装
        return collect([]);
    }

    /**
     * 最長連続記録を計算
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options オプション
     * @return int 最長連続日数
     */
    private function calculateBestStreak(int $childId, array $options): int
    {
        $activeDays = Stamp::where('child_id', $childId)
                          ->whereBetween('stamped_at', [$options['start_date'], $options['end_date']])
                          ->select(DB::raw('DATE(stamped_at) as date'))
                          ->distinct()
                          ->orderBy('date')
                          ->pluck('date')
                          ->map(fn($date) => Carbon::parse($date));
        
        if ($activeDays->isEmpty()) {
            return 0;
        }
        
        $maxStreak = 1;
        $currentStreak = 1;
        
        for ($i = 1; $i < $activeDays->count(); $i++) {
            if ($activeDays[$i]->diffInDays($activeDays[$i-1]) === 1) {
                $currentStreak++;
                $maxStreak = max($maxStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }
        
        return $maxStreak;
    }

    /**
     * 一貫性スコアを計算
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $options オプション
     * @return float 一貫性スコア（0-100）
     */
    private function calculateConsistencyScore(int $childId, array $options): float
    {
        $totalDays = $options['start_date']->diffInDays($options['end_date']) + 1;
        $activeDays = Stamp::where('child_id', $childId)
                          ->whereBetween('stamped_at', [$options['start_date'], $options['end_date']])
                          ->select(DB::raw('DATE(stamped_at) as date'))
                          ->distinct()
                          ->count();
        
        return $totalDays > 0 ? round(($activeDays / $totalDays) * 100, 1) : 0;
    }

    /**
     * 日付をグループ化用の文字列にフォーマット
     *
     * @param \Carbon\Carbon $date 日付
     * @param string $groupBy グループ化の種類
     * @return string フォーマット済み日付文字列
     */
    private function formatDateForGrouping(\Carbon\Carbon $date, string $groupBy): string
    {
        return match($groupBy) {
            'day' => $date->format('Y-m-d'),
            'week' => $date->format('Y') . '-' . $date->week(),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }

    /**
     * 欠損期間を0で補完
     *
     * @param Collection $data 元データ
     * @param array<string, mixed> $options オプション
     * @param string $groupBy グループ化の種類
     * @return Collection 補完されたデータ
     */
    private function fillMissingPeriods(Collection $data, array $options, string $groupBy): Collection
    {
        $period = CarbonPeriod::create(
            $options['start_date'],
            $this->getPeriodInterval($groupBy),
            $options['end_date']
        );
        
        $result = collect();
        
        foreach ($period as $date) {
            $key = $this->formatPeriodKey($date, $groupBy);
            $existing = $data->firstWhere('period', $key);
            
            $result->push([
                'period' => $key,
                'date' => $date->format('Y-m-d'),
                'count' => $existing ? $existing->count : 0,
                'unique_types' => $existing ? $existing->unique_types : 0,
            ]);
        }
        
        return $result;
    }

    /**
     * 期間間隔を取得
     *
     * @param string $groupBy グループ化の種類
     * @return string 期間間隔
     */
    private function getPeriodInterval(string $groupBy): string
    {
        return match($groupBy) {
            'day' => '1 day',
            'week' => '1 week',
            'month' => '1 month',
            default => '1 day',
        };
    }

    /**
     * 期間キーをフォーマット
     *
     * @param Carbon $date 日付
     * @param string $groupBy グループ化の種類
     * @return string フォーマットされたキー
     */
    private function formatPeriodKey(Carbon $date, string $groupBy): string
    {
        return match($groupBy) {
            'day' => $date->format('Y-m-d'),
            'week' => $date->format('Y-W'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y-m-d'),
        };
    }
}