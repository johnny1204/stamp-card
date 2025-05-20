<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Child;
use App\Models\Stamp;
use App\Models\Pokemon;
use App\Models\StampType;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * StatisticsControllerの統合テスト
 */
class StatisticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private Child $child;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト用データ作成
        $this->admin = Admin::create([
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
        ]);

        $this->child = Child::create([
            'name' => 'テスト太郎',
            'birth_date' => '2015-01-01',
            'target_stamps' => 10,
        ]);

        $this->createTestStamps();
    }

    private function createTestStamps(): void
    {
        $stampType = StampType::create([
            'name' => 'テストスタンプ',
            'icon' => '⭐',
            'color' => '#FFD700',
            'category' => 'help',
        ]);

        $pokemon = Pokemon::create([
            'name' => 'ピカチュウ',
            'type1' => 'でんき',
            'genus' => 'ねずみポケモン',
            'is_legendary' => false,
            'is_mythical' => false,
        ]);

        // テスト用スタンプを作成
        for ($i = 0; $i < 5; $i++) {
            Stamp::create([
                'child_id' => $this->child->id,
                'stamp_type_id' => $stampType->id,
                'pokemon_id' => $pokemon->id,
                'stamped_at' => Carbon::now()->subDays($i),
                'comment' => "テストスタンプ{$i}",
            ]);
        }
    }

    /**
     * 統計ダッシュボードページのテスト
     */
    public function testStatisticsDashboard(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.dashboard', $this->child->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Statistics/Dashboard')
                ->has('child')
                ->has('basic_statistics')
                ->has('stamp_type_statistics')
                ->has('pokemon_statistics')
                ->has('growth_chart_data')
                ->where('child.id', $this->child->id)
        );
    }

    /**
     * 詳細統計ページのテスト
     */
    public function testDetailedStatistics(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.detailed', [
                'child' => $this->child->id,
                'period' => 'daily',
                'limit' => 30,
                'days' => 30
            ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Statistics/Detailed')
                ->has('child')
                ->has('period_statistics')
                ->has('growth_chart_data')
                ->has('basic_statistics')
                ->has('filters')
                ->where('child.id', $this->child->id)
                ->where('filters.period', 'daily')
                ->where('filters.limit', 30)
                ->where('filters.days', 30)
        );
    }


    /**
     * 月間レポートページのテスト
     */
    public function testMonthlyReportPage(): void
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.monthly-report', [
                'child' => $this->child->id,
                'year' => $year,
                'month' => $month
            ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Statistics/MonthlyReport')
                ->has('child')
                ->has('report_data')
                ->has('navigation')
                ->where('child.id', $this->child->id)
                ->where('report_data.period.year', $year)
                ->where('report_data.period.month', $month)
        );
    }

    /**
     * 基本統計APIのテスト
     */
    public function testApiBasicStatistics(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.api.basic', $this->child->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_stamps',
                'today_stamps',
                'this_month_stamps',
                'this_year_stamps',
                'legendary_count',
                'mythical_count',
                'special_pokemon_rate',
            ],
            'child' => [
                'id',
                'name',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals(5, $data['data']['total_stamps']);
        $this->assertEquals($this->child->id, $data['child']['id']);
    }

    /**
     * 期間別統計APIのテスト
     */
    public function testApiPeriodStatistics(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.api.period', [
                'child' => $this->child->id,
                'period' => 'daily',
                'limit' => 7
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'period',
                'data',
                'total',
                'average',
            ],
            'child' => [
                'id',
                'name',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals('daily', $data['data']['period']);
        $this->assertCount(7, $data['data']['data']);
    }

    /**
     * スタンプ種類別統計APIのテスト
     */
    public function testApiStampTypeStatistics(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.api.stamp-types', $this->child->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'stamp_type' => [
                        'id',
                        'name',
                        'icon',
                        'color',
                        'category',
                    ],
                    'count',
                    'legendary_count',
                    'mythical_count',
                    'percentage',
                    'recent_stamps',
                ],
            ],
            'child' => [
                'id',
                'name',
            ],
            'period' => [
                'start_date',
                'end_date',
            ],
        ]);
    }

    /**
     * ポケモン統計APIのテスト
     */
    public function testApiPokemonStatistics(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.api.pokemon', $this->child->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_unique_pokemons',
                'common_pokemons',
                'legendary_pokemons',
                'mythical_pokemons',
                'collection_list',
                'most_encountered',
                'rarest_encounters',
            ],
            'child' => [
                'id',
                'name',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals(1, $data['data']['total_unique_pokemons']);
    }

    /**
     * 成長グラフデータAPIのテスト
     */
    public function testApiGrowthChartData(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.api.growth-chart', [
                'child' => $this->child->id,
                'days' => 30
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'period' => [
                    'start_date',
                    'end_date',
                    'days',
                ],
                'daily_data',
                'totals' => [
                    'total_stamps',
                    'average_per_day',
                    'max_daily_stamps',
                    'active_days',
                ],
            ],
            'child' => [
                'id',
                'name',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals(30, $data['data']['period']['days']);
        $this->assertCount(30, $data['data']['daily_data']);
    }

    /**
     * 月間レポートAPIのテスト
     */
    public function testApiMonthlyReport(): void
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.api.monthly-report', [
                'child' => $this->child->id,
                'year' => $year,
                'month' => $month
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'period' => [
                    'year',
                    'month',
                    'month_name',
                    'start_date',
                    'end_date',
                ],
                'basic_statistics',
                'stamp_type_statistics',
                'pokemon_statistics',
                'growth_data',
            ],
            'child' => [
                'id',
                'name',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals($year, $data['data']['period']['year']);
        $this->assertEquals($month, $data['data']['period']['month']);
    }

    /**
     * 認証なしでのアクセステスト
     */
    public function testUnauthenticatedAccess(): void
    {
        $response = $this->get(route('children.statistics.dashboard', $this->child->id));
        $response->assertRedirect(route('auth.login'));

        $response = $this->get(route('children.statistics.detailed', $this->child->id));
        $response->assertRedirect(route('auth.login'));


        $response = $this->get(route('children.statistics.monthly-report', $this->child->id));
        $response->assertRedirect(route('auth.login'));
    }

    /**
     * 存在しない子どものアクセステスト
     */
    public function testNonExistentChild(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.dashboard', ['child' => 99999]));
        
        $response->assertStatus(404);
    }

    /**
     * 無効なパラメータのテスト
     */
    public function testInvalidParameters(): void
    {
        // 無効な期間
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.detailed', [
                'child' => $this->child->id,
                'period' => 'invalid',
                'limit' => 400
            ]));

        $response->assertStatus(422);

        // 無効な年月
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.monthly-report', [
                'child' => $this->child->id,
                'year' => 2050,
                'month' => 13
            ]));

        $response->assertStatus(422);
    }

    /**
     * デフォルトパラメータのテスト
     */
    public function testDefaultParameters(): void
    {
        // デフォルトパラメータでの詳細統計
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.detailed', $this->child->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('filters.period', 'daily')
                ->where('filters.limit', 30)
                ->where('filters.days', 30)
        );

        // デフォルトパラメータでの月間レポート
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.monthly-report', $this->child->id));

        $response->assertStatus(200);
        $now = Carbon::now();
        $response->assertInertia(fn ($page) => 
            $page->where('report_data.period.year', $now->year)
                ->where('report_data.period.month', $now->month)
        );
    }

    /**
     * 日付範囲指定でのスタンプ種類別統計のテスト
     */
    public function testStampTypeStatisticsWithDateRange(): void
    {
        $startDate = Carbon::now()->subDays(3)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.statistics.api.stamp-types', [
                'child' => $this->child->id,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200);
        $response->assertJsonPath('period.start_date', $startDate);
        $response->assertJsonPath('period.end_date', $endDate);
    }
}