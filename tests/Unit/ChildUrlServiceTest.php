<?php

namespace Tests\Unit;

use App\Models\Child;
use App\Services\ChildUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * 子ども用URLサービスのテスト
 */
class ChildUrlServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChildUrlService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChildUrlService();
    }

    /**
     * スタンプカード用署名付きURL生成テスト
     */
    public function test_generates_stamp_cards_signed_url(): void
    {
        $child = Child::factory()->create();

        $url = $this->service->generateStampCardsUrl($child, 30);

        $this->assertStringContainsString('/child/' . $child->id . '/stamp-cards', $url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString('expires=', $url);
    }

    /**
     * スタンプ一覧用署名付きURL生成テスト
     */
    public function test_generates_stamps_signed_url(): void
    {
        $child = Child::factory()->create();

        $url = $this->service->generateStampsUrl($child, 30);

        $this->assertStringContainsString('/child/' . $child->id . '/stamps', $url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString('expires=', $url);
    }

    /**
     * 今日のスタンプ用署名付きURL生成テスト
     */
    public function test_generates_today_stamps_signed_url(): void
    {
        $child = Child::factory()->create();

        $url = $this->service->generateTodayStampsUrl($child, 30);

        $this->assertStringContainsString('/child/' . $child->id . '/today-stamps', $url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString('expires=', $url);
    }

    /**
     * 全URL生成テスト
     */
    public function test_generates_all_urls(): void
    {
        $child = Child::factory()->create();

        $urls = $this->service->generateAllUrls($child, 30);

        $this->assertArrayHasKey('stamp_cards', $urls);
        $this->assertArrayHasKey('stamps', $urls);
        $this->assertArrayHasKey('today_stamps', $urls);

        foreach ($urls as $url) {
            $this->assertStringContainsString('/child/' . $child->id, $url);
            $this->assertStringContainsString('signature=', $url);
        }
    }

    /**
     * カスタム有効期間のURL生成テスト
     */
    public function test_generates_url_with_custom_expiration(): void
    {
        $child = Child::factory()->create();

        $url = $this->service->generateStampCardsUrl($child, 7); // 7日間

        $this->assertStringContainsString('/child/' . $child->id . '/stamp-cards', $url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString('expires=', $url);
    }

    /**
     * 生成されたURLが実際に有効かテスト
     */
    public function test_generated_url_is_valid(): void
    {
        $child = Child::factory()->create();

        $url = $this->service->generateStampCardsUrl($child, 30);

        // URLから署名を検証
        $response = $this->get($url);
        $response->assertStatus(200);
    }

    /**
     * 期限切れURLのテスト
     */
    public function test_expired_url_is_invalid(): void
    {
        $child = Child::factory()->create();

        // 過去の日時で署名付きURLを生成
        $expiredUrl = URL::temporarySignedRoute(
            'child.stamp-cards',
            now()->subDay(), // 1日前
            ['child' => $child->id]
        );

        $response = $this->get($expiredUrl);
        $response->assertStatus(403); // Forbidden
    }
}
