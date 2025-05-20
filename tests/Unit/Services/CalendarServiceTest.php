<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CalendarService;
use App\Models\Child;
use App\Models\Stamp;
use App\Models\Pokemon;
use App\Models\StampType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * CalendarServiceのテスト
 */
class CalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private CalendarService $calendarService;
    private Child $child;
    private StampType $stampType;
    private Pokemon $pokemon;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calendarService = app(CalendarService::class);
        
        // テスト用データ作成
        $this->child = Child::create([
            'name' => 'テスト太郎',
            'birth_date' => '2015-01-01',
            'target_stamps' => 10,
        ]);

        $this->stampType = StampType::create([
            'name' => 'テストスタンプ',
            'icon' => '⭐',
            'color' => '#FFD700',
            'category' => 'help',
        ]);

        $this->pokemon = Pokemon::create([
            'name' => 'ピカチュウ',
            'type1' => 'でんき',
            'genus' => 'ねずみポケモン',
            'is_legendary' => false,
            'is_mythical' => false,
        ]);
    }

    /**
     * 月間カレンダーデータ取得のテスト
     */
    public function testGetMonthlyCalendar(): void
    {
        // 2024年2月のテストデータを作成
        $year = 2024;
        $month = 2;
        
        // 2月1日にスタンプを作成
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $this->pokemon->id,
            'stamped_at' => Carbon::create($year, $month, 1, 10, 0, 0),
            'comment' => '2月最初のスタンプ',
        ]);

        // 2月14日にスタンプを2個作成
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $this->pokemon->id,
            'stamped_at' => Carbon::create($year, $month, 14, 14, 0, 0),
            'comment' => 'バレンタインスタンプ1',
        ]);

        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $this->pokemon->id,
            'stamped_at' => Carbon::create($year, $month, 14, 15, 0, 0),
            'comment' => 'バレンタインスタンプ2',
        ]);

        $result = $this->calendarService->getMonthlyCalendar($this->child, $year, $month);

        // 基本構造の確認
        $this->assertArrayHasKey('year', $result);
        $this->assertArrayHasKey('month', $result);
        $this->assertArrayHasKey('calendar', $result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('total_stamps', $result);

        $this->assertEquals($year, $result['year']);
        $this->assertEquals($month, $result['month']);
        $this->assertEquals(3, $result['total_stamps']);

        // カレンダー構造の確認（週ごとの配列）
        $this->assertIsArray($result['calendar']);
        $this->assertGreaterThan(0, count($result['calendar']));

        // 統計情報の確認
        $this->assertEquals(3, $result['statistics']['total_stamps']);
        $this->assertEquals(0, $result['statistics']['legendary_count']);
        $this->assertEquals(0, $result['statistics']['mythical_count']);
        $this->assertEquals(1.5, $result['statistics']['average_per_day']); // 3個 / 2日 = 1.5
    }

    /**
     * 週間カレンダーデータ取得のテスト
     */
    public function testGetWeeklyCalendar(): void
    {
        $startOfWeek = Carbon::create(2024, 2, 5)->startOfWeek(); // 2024年2月5日の週の始まり

        // 週の中間にスタンプを作成
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $this->pokemon->id,
            'stamped_at' => $startOfWeek->copy()->addDays(3)->setTime(10, 0, 0), // 木曜日
            'comment' => '週間テストスタンプ',
        ]);

        $result = $this->calendarService->getWeeklyCalendar($this->child, $startOfWeek);

        // 基本構造の確認
        $this->assertArrayHasKey('start_of_week', $result);
        $this->assertArrayHasKey('end_of_week', $result);
        $this->assertArrayHasKey('week_days', $result);
        $this->assertArrayHasKey('total_stamps', $result);

        $this->assertEquals(1, $result['total_stamps']);
        $this->assertCount(7, $result['week_days']); // 7日分

        // 各日のデータ構造を確認
        foreach ($result['week_days'] as $day) {
            $this->assertArrayHasKey('date', $day);
            $this->assertArrayHasKey('day_of_week', $day);
            $this->assertArrayHasKey('day_name', $day);
            $this->assertArrayHasKey('stamps', $day);
            $this->assertArrayHasKey('stamps_count', $day);
            $this->assertArrayHasKey('is_today', $day);
        }
    }

    /**
     * 日別詳細データ取得のテスト
     */
    public function testGetDailyDetail(): void
    {
        $targetDate = Carbon::create(2024, 2, 10);

        // 指定日にスタンプを作成
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $this->pokemon->id,
            'stamped_at' => $targetDate->copy()->setTime(9, 30, 0),
            'comment' => '朝のスタンプ',
        ]);

        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $this->pokemon->id,
            'stamped_at' => $targetDate->copy()->setTime(18, 0, 0),
            'comment' => '夕方のスタンプ',
        ]);

        $result = $this->calendarService->getDailyDetail($this->child, $targetDate);

        // 基本構造の確認
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('day_name', $result);
        $this->assertArrayHasKey('stamps', $result);
        $this->assertArrayHasKey('stamps_count', $result);
        $this->assertArrayHasKey('stamp_type_statistics', $result);
        $this->assertArrayHasKey('is_today', $result);

        $this->assertEquals($targetDate->format('Y-m-d'), $result['date']);
        $this->assertEquals(2, $result['stamps_count']);
        $this->assertCount(2, $result['stamps']);

        // スタンプ種類別統計の確認
        $this->assertCount(1, $result['stamp_type_statistics']); // 1つのスタンプ種類
        $this->assertEquals(2, $result['stamp_type_statistics'][0]['count']);
    }

    /**
     * 今月のカレンダーデータ取得のテスト
     */
    public function testGetCurrentMonthCalendar(): void
    {
        // 現在の月にスタンプを作成
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $this->pokemon->id,
            'stamped_at' => Carbon::now(),
            'comment' => '今月のスタンプ',
        ]);

        $result = $this->calendarService->getCurrentMonthCalendar($this->child);

        $now = Carbon::now();
        $this->assertEquals($now->year, $result['year']);
        $this->assertEquals($now->month, $result['month']);
        $this->assertEquals(1, $result['total_stamps']);
    }

    /**
     * 今週のカレンダーデータ取得のテスト
     */
    public function testGetCurrentWeekCalendar(): void
    {
        // 今週にスタンプを作成
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $this->pokemon->id,
            'stamped_at' => Carbon::now(),
            'comment' => '今週のスタンプ',
        ]);

        $result = $this->calendarService->getCurrentWeekCalendar($this->child);

        $this->assertEquals(1, $result['total_stamps']);
        $this->assertCount(7, $result['week_days']);
    }

    /**
     * レジェンダリー・幻ポケモンの統計テスト
     */
    public function testCalendarWithSpecialPokemons(): void
    {
        // レジェンダリーポケモンを作成
        $legendary = Pokemon::create([
            'name' => 'フリーザー',
            'type1' => 'こおり',
            'type2' => 'ひこう',
            'genus' => 'れいとうポケモン',
            'is_legendary' => true,
            'is_mythical' => false,
        ]);

        // 幻のポケモンを作成
        $mythical = Pokemon::create([
            'name' => 'ミュウ',
            'type1' => 'エスパー',
            'genus' => 'しんしゅポケモン',
            'is_legendary' => false,
            'is_mythical' => true,
        ]);

        $year = 2024;
        $month = 3;

        // 各種ポケモンでスタンプを作成
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $this->pokemon->id, // 通常
            'stamped_at' => Carbon::create($year, $month, 1),
        ]);

        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $legendary->id, // レジェンダリー
            'stamped_at' => Carbon::create($year, $month, 2),
        ]);

        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $this->stampType->id,
            'pokemon_id' => $mythical->id, // 幻
            'stamped_at' => Carbon::create($year, $month, 3),
        ]);

        $result = $this->calendarService->getMonthlyCalendar($this->child, $year, $month);

        // 統計情報の確認
        $this->assertEquals(3, $result['statistics']['total_stamps']);
        $this->assertEquals(1, $result['statistics']['legendary_count']);
        $this->assertEquals(1, $result['statistics']['mythical_count']);

        // カレンダー内の特別ポケモンフラグ確認
        $calendar = $result['calendar'];
        $foundLegendaryDay = false;
        $foundMythicalDay = false;

        foreach ($calendar as $week) {
            foreach ($week as $day) {
                if ($day['has_legendary']) {
                    $foundLegendaryDay = true;
                }
                if ($day['has_mythical']) {
                    $foundMythicalDay = true;
                }
            }
        }

        $this->assertTrue($foundLegendaryDay);
        $this->assertTrue($foundMythicalDay);
    }

    /**
     * スタンプがない月のテスト
     */
    public function testEmptyMonthCalendar(): void
    {
        $result = $this->calendarService->getMonthlyCalendar($this->child, 2024, 1);

        $this->assertEquals(0, $result['total_stamps']);
        $this->assertEquals(0, $result['statistics']['total_stamps']);
        $this->assertEquals(0, $result['statistics']['legendary_count']);
        $this->assertEquals(0, $result['statistics']['mythical_count']);
        $this->assertEquals(0, $result['statistics']['average_per_day']);

        // カレンダー構造は存在する
        $this->assertIsArray($result['calendar']);
        $this->assertGreaterThan(0, count($result['calendar']));
    }
}