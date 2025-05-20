<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Child;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * 認証機能のテスト
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者パスワード設定画面の表示テスト
     */
    public function test_set_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/auth/set-password');

        $response->assertStatus(200);
    }

    /**
     * 管理者パスワード設定テスト
     */
    public function test_admin_can_set_password(): void
    {
        $response = $this->post('/auth/set-password', [
            'password' => 'test-password',
            'password_confirmation' => 'test-password',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('admins', ['id' => 1]);
    }

    /**
     * ログイン画面の表示テスト
     */
    public function test_login_screen_can_be_rendered(): void
    {
        Admin::createOrUpdate('test-password');

        $response = $this->get('/auth/login');

        $response->assertStatus(200);
    }

    /**
     * 正常なログインテスト
     */
    public function test_admin_can_authenticate_using_correct_password(): void
    {
        $admin = Admin::createOrUpdate('test-password');

        $response = $this->post('/auth/login', [
            'password' => 'test-password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAsAdmin();
    }

    /**
     * 不正なパスワードでのログインテスト
     */
    public function test_admin_cannot_authenticate_with_invalid_password(): void
    {
        $admin = Admin::createOrUpdate('test-password');

        $response = $this->post('/auth/login', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertAdminGuest();
    }

    /**
     * ログアウトテスト
     */
    public function test_admin_can_logout(): void
    {
        $admin = Admin::createOrUpdate('test-password');
        $this->authenticateAsAdmin();

        $response = $this->post('/auth/logout');

        $response->assertRedirect('/auth/login');
        $this->assertAdminGuest();
    }

    /**
     * 未認証での保護されたページアクセステスト
     */
    public function test_unauthenticated_user_cannot_access_protected_pages(): void
    {
        Admin::createOrUpdate('test-password');

        $response = $this->get('/');
        $response->assertRedirect('/auth/login');

        $response = $this->get('/children');
        $response->assertRedirect('/auth/login');
    }

    /**
     * 管理者未設定状態での保護されたページアクセステスト
     */
    public function test_user_redirected_to_set_password_when_no_admin_exists(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/auth/set-password');
    }

    /**
     * 子ども用署名付きURLのアクセステスト
     */
    public function test_child_can_access_with_signed_url(): void
    {
        $child = Child::factory()->create();

        // 署名付きURLを生成
        $signedUrl = URL::temporarySignedRoute(
            'child.stamp-cards',
            now()->addHour(),
            ['child' => $child->id]
        );

        $response = $this->get($signedUrl);
        $response->assertStatus(200);
    }

    /**
     * 子ども用無効な署名付きURLのアクセステスト
     */
    public function test_child_cannot_access_with_invalid_signed_url(): void
    {
        $child = Child::factory()->create();

        // 無効な署名のURLでアクセス
        $invalidUrl = "/child/{$child->id}/stamp-cards";

        $response = $this->get($invalidUrl);
        $response->assertStatus(403); // Forbidden
    }

    /**
     * 管理者として認証する
     */
    protected function authenticateAsAdmin(): void
    {
        $this->session(['admin_authenticated' => true]);
    }

    /**
     * 管理者として認証されているかチェック
     */
    protected function assertAuthenticatedAsAdmin(): void
    {
        $this->assertTrue(session('admin_authenticated', false));
    }

    /**
     * カスタムゲスト状態チェック
     */
    protected function assertAdminGuest(): void
    {
        $this->assertFalse(session('admin_authenticated', false));
    }
}