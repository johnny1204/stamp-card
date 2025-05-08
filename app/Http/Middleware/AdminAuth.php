<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理者認証ミドルウェア
 * 
 * 親（管理者）のみアクセス可能なルートを保護
 */
class AdminAuth
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 管理者が存在しない場合はパスワード設定画面へ
        if (!$this->authService->adminExists()) {
            return redirect()->route('auth.set-password');
        }
        
        // 認証済みでない場合はログイン画面へ
        if (!$this->authService->check()) {
            return redirect()->route('auth.login');
        }
        
        return $next($request);
    }
}