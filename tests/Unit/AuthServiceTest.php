<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_admin_exists_returns_false_when_no_admin(): void
    {
        $this->assertFalse($this->authService->adminExists());
    }

    public function test_admin_exists_returns_true_when_admin_exists(): void
    {
        Admin::createOrUpdate('testpassword');

        $this->assertTrue($this->authService->adminExists());
    }

    public function test_set_password_creates_admin(): void
    {
        $admin = $this->authService->setPassword('newpassword');

        $this->assertInstanceOf(Admin::class, $admin);
        $this->assertTrue($this->authService->adminExists());
    }

    public function test_attempt_returns_false_when_no_admin(): void
    {
        $result = $this->authService->attempt('anypassword');

        $this->assertFalse($result);
    }

    public function test_attempt_returns_false_with_wrong_password(): void
    {
        Admin::createOrUpdate('correctpassword');

        $result = $this->authService->attempt('wrongpassword');

        $this->assertFalse($result);
        $this->assertFalse($this->authService->check());
    }

    public function test_attempt_returns_true_with_correct_password(): void
    {
        Admin::createOrUpdate('correctpassword');

        $result = $this->authService->attempt('correctpassword');

        $this->assertTrue($result);
        $this->assertTrue($this->authService->check());
    }

    public function test_check_returns_false_when_not_authenticated(): void
    {
        $this->assertFalse($this->authService->check());
    }

    public function test_logout_clears_authentication(): void
    {
        Admin::createOrUpdate('testpassword');
        $this->authService->attempt('testpassword');
        
        $this->assertTrue($this->authService->check());
        
        $this->authService->logout();
        
        $this->assertFalse($this->authService->check());
    }
}
