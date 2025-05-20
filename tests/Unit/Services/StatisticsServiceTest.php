<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\StatisticsService;
use App\Models\Child;
use App\Models\Stamp;
use App\Models\Pokemon;
use App\Models\StampType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * StatisticsServiceのテスト
 */
class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private StatisticsService $statisticsService;
    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->statisticsService = app(StatisticsService::class);
        
        // テスト用データ作成
        $this->child = Child::create([
            'name' => 'テスト太郎',
            'birth_date' => '2015-01-01',
            'target_stamps' => 10,
        ]);

        $this->createTestData();
    }

    private function createTestData(): void
    {
        // スタンプ種類を作成
        $stampType1 = StampType::create([
            'name' => 'お手伝い',
            'icon' => '🏠',
            'color' => '#FFD700',
            'category' => 'help',
        ]);

        $stampType2 = StampType::create([
            'name' => '宿題',
            'icon' => '📚',
            'color' => '#4CAF50',
            'category' => 'lifestyle',
        ]);

        // ポケモンを作成
        $pikachu = Pokemon::create([
            'name' => 'ピカチュウ',
            'type1' => 'でんき',
            'genus' => 'ねずみポケモン',
            'is_legendary' => false,
            'is_mythical' => false,
        ]);

        $freezer = Pokemon::create([
            'name' => 'フリーザー',
            'type1' => 'こおり',
            'type2' => 'ひこう',
            'genus' => 'れいとうポケモン',
            'is_legendary' => true,
            'is_mythical' => false,
        ]);

        $mew = Pokemon::create([
            'name' => 'ミュウ',
            'type1' => 'エスパー',
            'genus' => 'しんしゅポケモン',
            'is_legendary' => false,
            'is_mythical' => true,
        ]);

        // 様々な日付でスタンプを作成
        $baseDate = Carbon::now()->startOfMonth();

        // 今日のスタンプ
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $stampType1->id,
            'pokemon_id' => $pikachu->id,
            'stamped_at' => Carbon::now(),
            'comment' => '今日のスタンプ',
        ]);

        // 昨日のスタンプ
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $stampType2->id,
            'pokemon_id' => $freezer->id,
            'stamped_at' => Carbon::now()->subDay(),
            'comment' => '昨日のスタンプ',
        ]);

        // 今月のスタンプ（複数日）
        for ($i = 1; $i <= 5; $i++) {
            Stamp::create([
                'child_id' => $this->child->id,
                'stamp_type_id' => $i % 2 === 0 ? $stampType1->id : $stampType2->id,
                'pokemon_id' => $pikachu->id,
                'stamped_at' => $baseDate->copy()->addDays($i * 2),
                'comment' => "スタンプ{$i}",
            ]);
        }

        // 幻のポケモンのスタンプ
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $stampType1->id,
            'pokemon_id' => $mew->id,
            'stamped_at' => $baseDate->copy()->addDays(15),
            'comment' => '幻のポケモン',
        ]);
    }

    /**
     * 基本統計取得のテスト
     */
    public function testGetBasicStatistics(): void
    {
        $result = $this->statisticsService->getBasicStatistics($this->child);

        // 基本構造の確認
        $this->assertArrayHasKey('total_stamps', $result);
        $this->assertArrayHasKey('today_stamps', $result);
        $this->assertArrayHasKey('this_month_stamps', $result);
        $this->assertArrayHasKey('this_year_stamps', $result);
        $this->assertArrayHasKey('legendary_count', $result);
        $this->assertArrayHasKey('mythical_count', $result);
        $this->assertArrayHasKey('special_pokemon_rate', $result);

        // 値の確認
        $this->assertEquals(8, $result['total_stamps']); // 合計8個
        $this->assertEquals(1, $result['today_stamps']); // 今日1個
        $this->assertEquals(8, $result['this_month_stamps']); // 今月8個（全て今月）
        $this->assertEquals(1, $result['legendary_count']); // レジェンダリー1個
        $this->assertEquals(1, $result['mythical_count']); // 幻1個
        $this->assertEquals(25.0, $result['special_pokemon_rate']); // (1+1)/8 * 100 = 25%
    }

    /**
     * 期間別統計取得のテスト（日別）
     */
    public function testGetPeriodStatisticsDaily(): void
    {
        $result = $this->statisticsService->getPeriodStatistics($this->child, 'daily', 7);

        $this->assertArrayHasKey('period', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('average', $result);

        $this->assertEquals('daily', $result['period']);
        $this->assertCount(7, $result['data']); // 7日分
        $this->assertGreaterThan(0, $result['total']);
    }

    /**
     * 期間別統計取得のテスト（週別）
     */
    public function testGetPeriodStatisticsWeekly(): void
    {
        $result = $this->statisticsService->getPeriodStatistics($this->child, 'weekly', 4);

        $this->assertEquals('weekly', $result['period']);
        $this->assertCount(4, $result['data']); // 4週分
        $this->assertGreaterThan(0, $result['total']);
    }

    /**
     * 期間別統計取得のテスト（月別）
     */
    public function testGetPeriodStatisticsMonthly(): void
    {
        $result = $this->statisticsService->getPeriodStatistics($this->child, 'monthly', 3);

        $this->assertEquals('monthly', $result['period']);
        $this->assertCount(3, $result['data']); // 3ヶ月分
        $this->assertGreaterThan(0, $result['total']);
    }

    /**
     * スタンプ種類別統計取得のテスト
     */
    public function testGetStampTypeStatistics(): void
    {
        $result = $this->statisticsService->getStampTypeStatistics($this->child);

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));

        // 最初の統計の構造確認
        $firstStat = $result[0];
        $this->assertArrayHasKey('stamp_type', $firstStat);
        $this->assertArrayHasKey('count', $firstStat);
        $this->assertArrayHasKey('legendary_count', $firstStat);
        $this->assertArrayHasKey('mythical_count', $firstStat);
        $this->assertArrayHasKey('percentage', $firstStat);
        $this->assertArrayHasKey('recent_stamps', $firstStat);

        // スタンプ種類情報の確認
        $this->assertArrayHasKey('id', $firstStat['stamp_type']);
        $this->assertArrayHasKey('name', $firstStat['stamp_type']);
        $this->assertArrayHasKey('icon', $firstStat['stamp_type']);
        $this->assertArrayHasKey('color', $firstStat['stamp_type']);
        $this->assertArrayHasKey('category', $firstStat['stamp_type']);

        // 最近のスタンプの件数確認（最大5個）
        $this->assertLessThanOrEqual(5, count($firstStat['recent_stamps']));
    }

    /**
     * ポケモン収集統計取得のテスト
     */
    public function testGetPokemonCollectionStatistics(): void
    {
        $result = $this->statisticsService->getPokemonCollectionStatistics($this->child);

        $this->assertArrayHasKey('total_unique_pokemons', $result);
        $this->assertArrayHasKey('common_pokemons', $result);
        $this->assertArrayHasKey('legendary_pokemons', $result);
        $this->assertArrayHasKey('mythical_pokemons', $result);
        $this->assertArrayHasKey('collection_list', $result);
        $this->assertArrayHasKey('most_encountered', $result);
        $this->assertArrayHasKey('rarest_encounters', $result);

        // 値の確認
        $this->assertEquals(3, $result['total_unique_pokemons']); // ピカチュウ、フリーザー、ミュウ
        $this->assertEquals(1, $result['common_pokemons']); // ピカチュウ
        $this->assertEquals(1, $result['legendary_pokemons']); // フリーザー
        $this->assertEquals(1, $result['mythical_pokemons']); // ミュウ

        // 最も多く出会ったポケモンの確認
        $this->assertNotNull($result['most_encountered']);
        $this->assertEquals('ピカチュウ', $result['most_encountered']['pokemon']['name']);

        // 幻のポケモンの確認
        $this->assertCount(1, $result['rarest_encounters']);
        $this->assertEquals('ミュウ', $result['rarest_encounters'][0]['pokemon']['name']);
    }

    /**
     * 成長グラフデータ取得のテスト
     */
    public function testGetGrowthChartData(): void
    {
        $result = $this->statisticsService->getGrowthChartData($this->child, 7);

        $this->assertArrayHasKey('period', $result);
        $this->assertArrayHasKey('daily_data', $result);
        $this->assertArrayHasKey('totals', $result);

        // 期間情報の確認
        $this->assertArrayHasKey('start_date', $result['period']);
        $this->assertArrayHasKey('end_date', $result['period']);
        $this->assertArrayHasKey('days', $result['period']);
        $this->assertEquals(7, $result['period']['days']);

        // 日別データの確認
        $this->assertCount(7, $result['daily_data']);
        foreach ($result['daily_data'] as $dayData) {
            $this->assertArrayHasKey('date', $dayData);
            $this->assertArrayHasKey('day_name', $dayData);
            $this->assertArrayHasKey('total_stamps', $dayData);
            $this->assertArrayHasKey('legendary_stamps', $dayData);
            $this->assertArrayHasKey('mythical_stamps', $dayData);
            $this->assertArrayHasKey('cumulative_total', $dayData);
        }

        // 合計情報の確認
        $this->assertArrayHasKey('total_stamps', $result['totals']);
        $this->assertArrayHasKey('average_per_day', $result['totals']);
        $this->assertArrayHasKey('max_daily_stamps', $result['totals']);
        $this->assertArrayHasKey('active_days', $result['totals']);
    }

    /**
     * 月間レポート生成のテスト
     */
    public function testGenerateMonthlyReport(): void
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $result = $this->statisticsService->generateMonthlyReport($this->child, $year, $month);

        $this->assertArrayHasKey('period', $result);
        $this->assertArrayHasKey('basic_statistics', $result);
        $this->assertArrayHasKey('stamp_type_statistics', $result);
        $this->assertArrayHasKey('pokemon_statistics', $result);
        $this->assertArrayHasKey('growth_data', $result);

        // 期間情報の確認
        $this->assertEquals($year, $result['period']['year']);
        $this->assertEquals($month, $result['period']['month']);
        $this->assertArrayHasKey('month_name', $result['period']);
        $this->assertArrayHasKey('start_date', $result['period']);
        $this->assertArrayHasKey('end_date', $result['period']);
    }

    /**
     * 期間指定でのスタンプ種類別統計のテスト
     */
    public function testGetStampTypeStatisticsWithDateRange(): void
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $result = $this->statisticsService->getStampTypeStatistics($this->child, $startDate, $endDate);

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));

        // 期間内のスタンプのみが対象になっていることを確認
        $totalCount = array_sum(array_column($result, 'count'));
        $this->assertGreaterThan(0, $totalCount);
    }

    /**
     * スタンプがない子どもの統計テスト
     */
    public function testStatisticsWithNoStamps(): void
    {
        $emptyChild = Child::create([
            'name' => 'スタンプなし太郎',
            'birth_date' => '2015-01-01',
            'target_stamps' => 10,
        ]);

        $basicStats = $this->statisticsService->getBasicStatistics($emptyChild);

        $this->assertEquals(0, $basicStats['total_stamps']);
        $this->assertEquals(0, $basicStats['today_stamps']);
        $this->assertEquals(0, $basicStats['this_month_stamps']);
        $this->assertEquals(0, $basicStats['legendary_count']);
        $this->assertEquals(0, $basicStats['mythical_count']);
        $this->assertEquals(0, $basicStats['special_pokemon_rate']);

        $pokemonStats = $this->statisticsService->getPokemonCollectionStatistics($emptyChild);
        $this->assertEquals(0, $pokemonStats['total_unique_pokemons']);
        $this->assertNull($pokemonStats['most_encountered']);
        $this->assertEmpty($pokemonStats['rarest_encounters']);
    }
}