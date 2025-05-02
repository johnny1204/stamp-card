<?php

namespace App\Services;

use App\Models\Child;
use App\Models\Stamp;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * 統計機能関連のビジネスロジックを担当するサービス
 */
class StatisticsService extends BaseService
{
    /**
     * 子どもの基本統計を取得
     *
     * @param Child $child 対象の子ども
     * @return array<string, mixed> 基本統計データ
     */
    public function getBasicStatistics(Child $child): array
    {
        $totalStamps = $child->stamps()->count();
        $todayStamps = $child->stamps()->today()->count();
        $thisMonthStamps = $child->stamps()->thisMonth()->count();
        $thisYearStamps = $child->stamps()->whereYear('stamped_at', Carbon::now()->year)->count();

        // レアポケモン統計
        $legendaryCount = $child->stamps()->whereHas('pokemon', function ($query) {
            $query->where('is_legendary', true);
        })->count();

        $mythicalCount = $child->stamps()->whereHas('pokemon', function ($query) {
            $query->where('is_mythical', true);
        })->count();

        return [
            'total_stamps' => $totalStamps,
            'today_stamps' => $todayStamps,
            'this_month_stamps' => $thisMonthStamps,
            'this_year_stamps' => $thisYearStamps,
            'legendary_count' => $legendaryCount,
            'mythical_count' => $mythicalCount,
            'special_pokemon_rate' => $totalStamps > 0 ? round(($legendaryCount + $mythicalCount) / $totalStamps * 100, 1) : 0,
        ];
    }

    /**
     * 期間別スタンプ統計を取得
     *
     * @param Child $child 対象の子ども
     * @param string $period 期間（daily, weekly, monthly）
     * @param int $limit 取得期間数制限
     * @return array<string, mixed> 期間別統計データ
     */
    public function getPeriodStatistics(Child $child, string $period = 'daily', int $limit = 30): array
    {
        switch ($period) {
            case 'weekly':
                return $this->getWeeklyStatistics($child, $limit);
            case 'monthly':
                return $this->getMonthlyStatistics($child, $limit);
            case 'daily':
            default:
                return $this->getDailyStatistics($child, $limit);
        }
    }

    /**
     * スタンプ種類別統計を取得
     *
     * @param Child $child 対象の子ども
     * @param Carbon|null $startDate 開始日（指定しない場合は全期間）
     * @param Carbon|null $endDate 終了日（指定しない場合は現在まで）
     * @return array<array<string, mixed>> スタンプ種類別統計
     */
    public function getStampTypeStatistics(Child $child, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = $child->stamps()->with(['stampType', 'pokemon']);

        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }

        $stamps = $query->get();

        return $stamps->groupBy('stamp_type_id')
            ->map(function ($typeStamps) {
                $firstStamp = $typeStamps->first();
                $legendaryCount = $typeStamps->filter(fn($s) => $s->pokemon->is_legendary)->count();
                $mythicalCount = $typeStamps->filter(fn($s) => $s->pokemon->is_mythical)->count();

                return [
                    'stamp_type' => [
                        'id' => $firstStamp->stampType->id,
                        'name' => $firstStamp->stampType->name,
                        'icon' => $firstStamp->stampType->icon,
                        'color' => $firstStamp->stampType->color,
                        'category' => $firstStamp->stampType->category,
                    ],
                    'count' => $typeStamps->count(),
                    'legendary_count' => $legendaryCount,
                    'mythical_count' => $mythicalCount,
                    'percentage' => 0, // 後で計算
                    'recent_stamps' => $typeStamps->sortByDesc('stamped_at')->take(5)->values()->toArray(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->map(function ($stat, $index) use ($stamps) {
                $stat['percentage'] = $stamps->count() > 0 ? round($stat['count'] / $stamps->count() * 100, 1) : 0;
                return $stat;
            })
            ->toArray();
    }

    /**
     * ポケモン収集統計を取得
     *
     * @param Child $child 対象の子ども
     * @return array<string, mixed> ポケモン収集統計
     */
    public function getPokemonCollectionStatistics(Child $child): array
    {
        $collectedPokemons = $child->stamps()
            ->with('pokemon')
            ->get()
            ->groupBy('pokemon_id')
            ->map(function ($pokemonStamps) {
                $pokemon = $pokemonStamps->first()->pokemon;
                return [
                    'pokemon' => [
                        'id' => $pokemon->id,
                        'name' => $pokemon->name,
                        'type1' => $pokemon->type1,
                        'type2' => $pokemon->type2,
                        'genus' => $pokemon->genus,
                        'is_legendary' => $pokemon->is_legendary,
                        'is_mythical' => $pokemon->is_mythical,
                    ],
                    'count' => $pokemonStamps->count(),
                    'first_encounter' => $pokemonStamps->min('stamped_at'),
                    'last_encounter' => $pokemonStamps->max('stamped_at'),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $totalUniquePokemons = $collectedPokemons->count();
        $commonPokemons = $collectedPokemons->filter(fn($p) => !$p['pokemon']['is_legendary'] && !$p['pokemon']['is_mythical'])->count();
        $legendaryPokemons = $collectedPokemons->filter(fn($p) => $p['pokemon']['is_legendary'])->count();
        $mythicalPokemons = $collectedPokemons->filter(fn($p) => $p['pokemon']['is_mythical'])->count();

        return [
            'total_unique_pokemons' => $totalUniquePokemons,
            'common_pokemons' => $commonPokemons,
            'legendary_pokemons' => $legendaryPokemons,
            'mythical_pokemons' => $mythicalPokemons,
            'collection_list' => $collectedPokemons->toArray(),
            'most_encountered' => $collectedPokemons->first(),
            'rarest_encounters' => $collectedPokemons->filter(fn($p) => $p['pokemon']['is_mythical'])->values()->toArray(),
        ];
    }

    /**
     * 成長グラフデータを取得
     *
     * @param Child $child 対象の子ども
     * @param int $days 過去何日分のデータを取得するか
     * @return array<string, mixed> 成長グラフデータ
     */
    public function getGrowthChartData(Child $child, int $days = 30): array
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days - 1);

        $stamps = $child->stamps()
            ->with('pokemon')
            ->betweenDates($startDate->startOfDay(), $endDate->endOfDay())
            ->get();

        $dailyData = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayStamps = $stamps->filter(function ($stamp) use ($currentDate) {
                return Carbon::parse($stamp->stamped_at)->isSameDay($currentDate);
            });

            $dailyData[] = [
                'date' => $dateKey,
                'day_name' => $currentDate->format('D'),
                'total_stamps' => $dayStamps->count(),
                'legendary_stamps' => $dayStamps->filter(fn($s) => $s->pokemon->is_legendary)->count(),
                'mythical_stamps' => $dayStamps->filter(fn($s) => $s->pokemon->is_mythical)->count(),
                'cumulative_total' => 0, // 後で計算
            ];

            $currentDate->addDay();
        }

        // 累積合計を計算
        $cumulative = 0;
        foreach ($dailyData as $index => $day) {
            $cumulative += $day['total_stamps'];
            $dailyData[$index]['cumulative_total'] = $cumulative;
        }

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => $days,
            ],
            'daily_data' => $dailyData,
            'totals' => [
                'total_stamps' => $cumulative,
                'average_per_day' => $days > 0 ? round($cumulative / $days, 1) : 0,
                'max_daily_stamps' => collect($dailyData)->max('total_stamps'),
                'active_days' => collect($dailyData)->filter(fn($d) => $d['total_stamps'] > 0)->count(),
            ],
        ];
    }

    /**
     * 日別統計データを取得
     *
     * @param Child $child 対象の子ども
     * @param int $limit 取得日数
     * @return array<string, mixed> 日別統計データ
     */
    private function getDailyStatistics(Child $child, int $limit): array
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($limit - 1);

        $stamps = $child->stamps()
            ->betweenDates($startDate->startOfDay(), $endDate->endOfDay())
            ->selectRaw('DATE(stamped_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $dailyData = [];
        $currentDate = $endDate->copy();

        for ($i = 0; $i < $limit; $i++) {
            $dateKey = $currentDate->format('Y-m-d');
            $dailyData[] = [
                'date' => $dateKey,
                'day_name' => $currentDate->format('D'),
                'count' => $stamps[$dateKey] ?? 0,
            ];
            $currentDate->subDay();
        }

        return [
            'period' => 'daily',
            'data' => $dailyData,
            'total' => array_sum($stamps),
            'average' => count($stamps) > 0 ? round(array_sum($stamps) / count($stamps), 1) : 0,
        ];
    }

    /**
     * 週別統計データを取得
     *
     * @param Child $child 対象の子ども
     * @param int $limit 取得週数
     * @return array<string, mixed> 週別統計データ
     */
    private function getWeeklyStatistics(Child $child, int $limit): array
    {
        $weeklyData = [];
        $currentWeek = Carbon::now()->startOfWeek();

        for ($i = 0; $i < $limit; $i++) {
            $weekStart = $currentWeek->copy()->subWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $weekStamps = $child->stamps()
                ->betweenDates($weekStart, $weekEnd)
                ->count();

            $weeklyData[] = [
                'week_start' => $weekStart->format('Y-m-d'),
                'week_end' => $weekEnd->format('Y-m-d'),
                'week_label' => $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
                'count' => $weekStamps,
            ];
        }

        return [
            'period' => 'weekly',
            'data' => array_reverse($weeklyData),
            'total' => array_sum(array_column($weeklyData, 'count')),
            'average' => count($weeklyData) > 0 ? round(array_sum(array_column($weeklyData, 'count')) / count($weeklyData), 1) : 0,
        ];
    }

    /**
     * 月別統計データを取得
     *
     * @param Child $child 対象の子ども
     * @param int $limit 取得月数
     * @return array<string, mixed> 月別統計データ
     */
    private function getMonthlyStatistics(Child $child, int $limit): array
    {
        $monthlyData = [];
        $currentMonth = Carbon::now()->startOfMonth();

        for ($i = 0; $i < $limit; $i++) {
            $monthStart = $currentMonth->copy()->subMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();

            $monthStamps = $child->stamps()
                ->betweenDates($monthStart, $monthEnd)
                ->count();

            $monthlyData[] = [
                'month' => $monthStart->format('Y-m'),
                'month_label' => $monthStart->format('Y年n月'),
                'count' => $monthStamps,
            ];
        }

        return [
            'period' => 'monthly',
            'data' => array_reverse($monthlyData),
            'total' => array_sum(array_column($monthlyData, 'count')),
            'average' => count($monthlyData) > 0 ? round(array_sum(array_column($monthlyData, 'count')) / count($monthlyData), 1) : 0,
        ];
    }

    /**
     * 月間レポート生成
     *
     * @param Child $child 対象の子ども
     * @param int $year 年
     * @param int $month 月
     * @return array<string, mixed> 月間レポートデータ
     */
    public function generateMonthlyReport(Child $child, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $basicStats = $this->getBasicStatisticsForPeriod($child, $startDate, $endDate);
        $stampTypeStats = $this->getStampTypeStatistics($child, $startDate, $endDate);
        $pokemonStats = $this->getPokemonCollectionStatisticsForPeriod($child, $startDate, $endDate);
        $growthData = $this->getGrowthChartDataForPeriod($child, $startDate, $endDate);

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'month_name' => $startDate->format('Y年n月'),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'basic_statistics' => $basicStats,
            'stamp_type_statistics' => $stampTypeStats,
            'pokemon_statistics' => $pokemonStats,
            'growth_data' => $growthData,
        ];
    }

    /**
     * 指定期間の基本統計を取得
     */
    private function getBasicStatisticsForPeriod(Child $child, Carbon $startDate, Carbon $endDate): array
    {
        $stamps = $child->stamps()->betweenDates($startDate, $endDate)->with('pokemon')->get();
        
        return [
            'total_stamps' => $stamps->count(),
            'legendary_count' => $stamps->filter(fn($s) => $s->pokemon->is_legendary)->count(),
            'mythical_count' => $stamps->filter(fn($s) => $s->pokemon->is_mythical)->count(),
            'unique_pokemons' => $stamps->groupBy('pokemon_id')->count(),
            'days_with_stamps' => $stamps->groupBy(fn($s) => Carbon::parse($s->stamped_at)->format('Y-m-d'))->count(),
        ];
    }

    /**
     * 指定期間のポケモン収集統計を取得
     */
    private function getPokemonCollectionStatisticsForPeriod(Child $child, Carbon $startDate, Carbon $endDate): array
    {
        $stamps = $child->stamps()->betweenDates($startDate, $endDate)->with('pokemon')->get();
        
        $pokemonCounts = $stamps->groupBy('pokemon_id')->map(function ($pokemonStamps) {
            return [
                'pokemon' => $pokemonStamps->first()->pokemon,
                'count' => $pokemonStamps->count(),
            ];
        })->sortByDesc('count')->values();

        return [
            'unique_pokemons' => $pokemonCounts->count(),
            'most_encountered' => $pokemonCounts->first(),
            'rarest_encounters' => $pokemonCounts->filter(fn($p) => $p['pokemon']->is_mythical)->values()->toArray(),
        ];
    }

    /**
     * 指定期間の成長データを取得
     */
    private function getGrowthChartDataForPeriod(Child $child, Carbon $startDate, Carbon $endDate): array
    {
        $stamps = $child->stamps()->betweenDates($startDate, $endDate)->get();
        
        $dailyData = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dayStamps = $stamps->filter(function ($stamp) use ($currentDate) {
                return Carbon::parse($stamp->stamped_at)->isSameDay($currentDate);
            });
            
            $dailyData[] = [
                'date' => $currentDate->format('Y-m-d'),
                'count' => $dayStamps->count(),
            ];
            
            $currentDate->addDay();
        }

        return [
            'daily_data' => $dailyData,
            'total_stamps' => $stamps->count(),
            'average_per_day' => count($dailyData) > 0 ? round($stamps->count() / count($dailyData), 1) : 0,
        ];
    }
}