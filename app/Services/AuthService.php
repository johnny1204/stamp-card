<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Session;

/**
 * 認証関連のビジネスロジックを担当するサービス
 */
class AuthService extends BaseService
{
    /** @var string セッションキー */
    private const SESSION_KEY = 'admin_authenticated';

    /**
     * 管理者認証を試行する
     *
     * @param string $password パスワード
     * @return bool 認証成功可否
     */
    public function attempt(string $password): bool
    {
        $admin = Admin::first();
        
        if (!$admin) {
            return false;
        }
        
        if ($admin->checkPassword($password)) {
            Session::put(self::SESSION_KEY, true);
            return true;
        }
        
        return false;
    }

    /**
     * 認証済みかチェックする
     *
     * @return bool 認証済みかどうか
     */
    public function check(): bool
    {
        return Session::get(self::SESSION_KEY, false);
    }

    /**
     * ログアウトする
     *
     * @return void
     */
    public function logout(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * 管理者パスワードを設定する
     *
     * @param string $password 新しいパスワード
     * @return Admin 管理者インスタンス
     */
    public function setPassword(string $password): Admin
    {
        return $this->sqliteTransaction(function () use ($password) {
            return Admin::createOrUpdate($password);
        });
    }

    /**
     * 管理者が存在するかチェック
     *
     * @return bool 管理者が存在するか
     */
    public function adminExists(): bool
    {
        return Admin::exists();
    }
}