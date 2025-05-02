<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 認証コントローラー
 * 
 * 簡単なパスワード認証機能を提供
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * ログイン画面を表示
     *
     * @return \Inertia\Response
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login', [
            'adminExists' => $this->authService->adminExists(),
        ]);
    }

    /**
     * ログイン処理
     *
     * @param LoginRequest $request
     * @return RedirectResponse
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        
        if ($this->authService->attempt($credentials['password'])) {
            return redirect()->intended(route('home'))
                ->with('success', 'ログインしました');
        }
        
        return back()->withErrors([
            'password' => 'パスワードが間違っています。',
        ])->onlyInput('password');
    }

    /**
     * ログアウト処理
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logout();
        
        return redirect()->route('auth.login')
            ->with('success', 'ログアウトしました');
    }

    /**
     * パスワード設定画面を表示
     *
     * @return \Inertia\Response
     */
    public function showSetPassword(): Response
    {
        return Inertia::render('Auth/SetPassword');
    }

    /**
     * パスワード設定処理
     *
     * @param SetPasswordRequest $request
     * @return RedirectResponse
     */
    public function setPassword(SetPasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        $this->authService->setPassword($data['password']);
        $this->authService->attempt($data['password']); // 自動ログイン
        
        return redirect()->route('home')
            ->with('success', 'パスワードを設定しました');
    }
}
