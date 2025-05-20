<?php

namespace Tests\Unit;

use App\Models\Child;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 子どもの年齢計算機能のテスト
 */
class ChildAgeCalculationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 年齢計算の基本テスト
     */
    public function test_calculates_age_correctly(): void
    {
        // 5年前の誕生日を設定
        $birthDate = Carbon::now()->subYears(5)->format('Y-m-d');
        $child = Child::factory()->withBirthDate($birthDate)->create();

        $this->assertEquals(5, $child->age);
    }

    /**
     * 誕生日前後での年齢計算テスト
     */
    public function test_calculates_age_before_and_after_birthday(): void
    {
        // 今日が誕生日の場合
        $today = Carbon::now();
        $birthDateToday = $today->copy()->subYears(10)->format('Y-m-d');
        $childToday = Child::factory()->withBirthDate($birthDateToday)->create();
        $this->assertEquals(10, $childToday->age);

        // 誕生日前の場合（まだ9歳）
        $beforeBirthday = $today->copy()->subYears(10)->addDay()->format('Y-m-d');
        $childBefore = Child::factory()->withBirthDate($beforeBirthday)->create();
        $this->assertEquals(9, $childBefore->age);

        // 誕生日後の場合（すでに10歳）
        $afterBirthday = $today->copy()->subYears(10)->subDay()->format('Y-m-d');
        $childAfter = Child::factory()->withBirthDate($afterBirthday)->create();
        $this->assertEquals(10, $childAfter->age);
    }

    /**
     * 誕生日が設定されていない場合のテスト
     */
    public function test_returns_null_when_birth_date_not_set(): void
    {
        $child = Child::factory()->create(['birth_date' => null]);

        $this->assertNull($child->age);
    }

    /**
     * 指定日での年齢計算テスト
     */
    public function test_calculates_age_at_specific_date(): void
    {
        $birthDate = '2015-06-15'; // 2015年6月15日生まれ
        $child = Child::factory()->withBirthDate($birthDate)->create();

        // 2020年6月14日時点では4歳
        $this->assertEquals(4, $child->getAgeAtDate('2020-06-14'));

        // 2020年6月15日時点では5歳
        $this->assertEquals(5, $child->getAgeAtDate('2020-06-15'));

        // 2020年12月31日時点では5歳
        $this->assertEquals(5, $child->getAgeAtDate('2020-12-31'));
    }

    /**
     * 年齢グループ取得テスト
     */
    public function test_gets_age_group_correctly(): void
    {
        // 2歳以下
        $child1 = Child::factory()->withAge(1)->create();
        $this->assertEquals('2歳以下', $child1->age_group);

        // 3-5歳
        $child2 = Child::factory()->withAge(4)->create();
        $this->assertEquals('3-5歳', $child2->age_group);

        // 6-8歳
        $child3 = Child::factory()->withAge(7)->create();
        $this->assertEquals('6-8歳', $child3->age_group);

        // 9-12歳
        $child4 = Child::factory()->withAge(10)->create();
        $this->assertEquals('9-12歳', $child4->age_group);

        // 13歳以上
        $child5 = Child::factory()->withAge(15)->create();
        $this->assertEquals('13歳以上', $child5->age_group);

        // 誕生日未設定
        $child6 = Child::factory()->create(['birth_date' => null]);
        $this->assertEquals('未設定', $child6->age_group);
    }

    /**
     * うるう年の計算テスト
     */
    public function test_calculates_age_correctly_for_leap_year(): void
    {
        // うるう年（2020年）の2月29日生まれ
        $birthDate = '2020-02-29';
        $child = Child::factory()->withBirthDate($birthDate)->create();

        // 2021年2月28日時点では0歳
        $this->assertEquals(0, $child->getAgeAtDate('2021-02-28'));

        // 2021年3月1日時点では1歳
        $this->assertEquals(1, $child->getAgeAtDate('2021-03-01'));

        // 2024年2月29日時点では4歳
        $this->assertEquals(4, $child->getAgeAtDate('2024-02-29'));
    }

    /**
     * Carbonオブジェクトでの日付指定テスト
     */
    public function test_accepts_carbon_date_for_age_calculation(): void
    {
        $birthDate = '2015-06-15';
        $child = Child::factory()->withBirthDate($birthDate)->create();

        $carbonDate = Carbon::parse('2020-06-15');
        $this->assertEquals(5, $child->getAgeAtDate($carbonDate));
    }

    /**
     * エッジケースのテスト
     */
    public function test_edge_cases(): void
    {
        // 生まれたばかり（今日生まれ）
        $today = Carbon::now()->format('Y-m-d');
        $newborn = Child::factory()->withBirthDate($today)->create();
        $this->assertEquals(0, $newborn->age);

        // 未来の誕生日（エラーケース）
        $future = Carbon::now()->addDay()->format('Y-m-d');
        $futureChild = Child::factory()->withBirthDate($future)->create();
        // 未来の日付でも年齢は負にならず、Carbonが適切に処理する
        $this->assertLessThan(1, $futureChild->age);
    }
}