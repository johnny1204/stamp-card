<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * ベースサービスクラス
 * 
 * 全サービスクラスの基底クラスとして共通機能を提供
 */
abstract class BaseService
{
    /**
     * トランザクション内で処理を実行
     *
     * @param callable $callback 実行する処理
     * @param string|null $connection データベース接続名
     * @return mixed 処理結果
     * @throws \Exception
     */
    protected function transaction(callable $callback, ?string $connection = null)
    {
        return DB::connection($connection)->transaction($callback);
    }

    /**
     * SQLite接続でトランザクション実行
     *
     * @param callable $callback 実行する処理
     * @return mixed 処理結果
     * @throws \Exception
     */
    protected function sqliteTransaction(callable $callback)
    {
        return $this->transaction($callback, 'sqlite');
    }

    /**
     * MySQL接続でトランザクション実行
     *
     * @param callable $callback 実行する処理
     * @return mixed 処理結果
     * @throws \Exception
     */
    protected function mysqlTransaction(callable $callback)
    {
        return $this->transaction($callback, 'mysql');
    }
}