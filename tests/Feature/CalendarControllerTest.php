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
 * CalendarControllerの統合テスト
 */
class CalendarControllerTest extends TestCase
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
        Stamp::create([
            'child_id' => $this->child->id,
            'stamp_type_id' => $stampType->id,
            'pokemon_id' => $pokemon->id,
            'stamped_at' => Carbon::create(2024, 2, 15, 10, 0, 0),
            'comment' => 'テストスタンプ',
        ]);
    }

    /**
     * 月間カレンダーページのテスト
     */
    public function testMonthlyCalendarPage(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.monthly', [
                'child' => $this->child->id,
                'year' => 2024,
                'month' => 2
            ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Calendar/Monthly')
                ->has('child')
                ->has('calendar_data')
                ->has('navigation')
                ->where('child.id', $this->child->id)
                ->where('calendar_data.year', 2024)
                ->where('calendar_data.month', 2)
                ->where('calendar_data.total_stamps', 1)
        );
    }

    /**
     * 週間カレンダーページのテスト
     */
    public function testWeeklyCalendarPage(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.weekly', [
                'child' => $this->child->id,
                'date' => '2024-02-15'
            ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Calendar/Weekly')
                ->has('child')
                ->has('calendar_data')
                ->has('navigation')
                ->where('child.id', $this->child->id)
                ->where('calendar_data.total_stamps', 1)
        );
    }

    /**
     * 日別詳細ページのテスト
     */
    public function testDailyCalendarPage(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.daily', [
                'child' => $this->child->id,
                'date' => '2024-02-15'
            ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Calendar/Daily')
                ->has('child')
                ->has('daily_data')
                ->has('navigation')
                ->where('child.id', $this->child->id)
                ->where('daily_data.stamps_count', 1)
        );
    }

    /**
     * 月間カレンダーAPIのテスト
     */
    public function testMonthlyCalendarApi(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.api.monthly', [
                'child' => $this->child->id,
                'year' => 2024,
                'month' => 2
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'year',
                'month',
                'calendar',
                'statistics' => [
                    'total_stamps',
                    'legendary_count',
                    'mythical_count',
                    'stamp_type_statistics',
                    'daily_statistics',
                    'average_per_day',
                ],
                'total_stamps',
            ],
            'child' => [
                'id',
                'name',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals(2024, $data['data']['year']);
        $this->assertEquals(2, $data['data']['month']);
        $this->assertEquals(1, $data['data']['total_stamps']);
    }

    /**
     * 週間カレンダーAPIのテスト
     */
    public function testWeeklyCalendarApi(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.api.weekly', [
                'child' => $this->child->id,
                'date' => '2024-02-15'
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'start_of_week',
                'end_of_week',
                'week_days',
                'total_stamps',
            ],
            'child' => [
                'id',
                'name',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals(1, $data['data']['total_stamps']);
        $this->assertCount(7, $data['data']['week_days']);
    }

    /**
     * 日別詳細APIのテスト
     */
    public function testDailyCalendarApi(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.api.daily', [
                'child' => $this->child->id,
                'date' => '2024-02-15'
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'date',
                'day_name',
                'stamps',
                'stamps_count',
                'stamp_type_statistics',
                'is_today',
            ],
            'child' => [
                'id',
                'name',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals('2024-02-15', $data['data']['date']);
        $this->assertEquals(1, $data['data']['stamps_count']);
    }

    /**
     * 認証なしでのアクセステスト
     */
    public function testUnauthenticatedAccess(): void
    {
        $response = $this->get(route('children.calendar.monthly', $this->child->id));
        $response->assertRedirect(route('auth.login'));

        $response = $this->get(route('children.calendar.weekly', $this->child->id));
        $response->assertRedirect(route('auth.login'));

        $response = $this->get(route('children.calendar.daily', $this->child->id));
        $response->assertRedirect(route('auth.login'));
    }

    /**
     * 存在しない子どものアクセステスト
     */
    public function testNonExistentChild(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.monthly', ['child' => 99999]));
        
        $response->assertStatus(404);
    }

    /**
     * 無効なパラメータのテスト
     */
    public function testInvalidParameters(): void
    {
        // 無効な年月
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.monthly', [
                'child' => $this->child->id,
                'year' => 2050,
                'month' => 13
            ]));

        $response->assertStatus(422);

        // 無効な日付
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.daily', [
                'child' => $this->child->id,
                'date' => 'invalid-date'
            ]));

        $response->assertStatus(422);
    }

    /**
     * デフォルトパラメータのテスト
     */
    public function testDefaultParameters(): void
    {
        // 現在の年月でアクセス
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.monthly', $this->child->id));

        $response->assertStatus(200);
        
        $now = Carbon::now();
        $response->assertInertia(fn ($page) => 
            $page->where('calendar_data.year', $now->year)
                ->where('calendar_data.month', $now->month)
        );

        // 今日の日付でアクセス
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.daily', $this->child->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('daily_data.date', $now->format('Y-m-d'))
        );
    }

    /**
     * 複数のスタンプがある日のテスト
     */
    public function testMultipleStampsDay(): void
    {
        $stampType = StampType::first();
        $pokemon = Pokemon::first();

        // 同じ日に複数のスタンプを作成
        for ($i = 0; $i < 5; $i++) {
            Stamp::create([
                'child_id' => $this->child->id,
                'stamp_type_id' => $stampType->id,
                'pokemon_id' => $pokemon->id,
                'stamped_at' => Carbon::create(2024, 2, 20, 10 + $i, 0, 0),
                'comment' => "スタンプ{$i}",
            ]);
        }

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('children.calendar.daily', [
                'child' => $this->child->id,
                'date' => '2024-02-20'
            ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->where('daily_data.stamps_count', 5)
        );
    }
}