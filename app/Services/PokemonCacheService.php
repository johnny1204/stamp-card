<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * ポケモン画像・鳴き声のサーバーサイドキャッシュサービス
 */
class PokemonCacheService
{
    private const CACHE_DURATION = 7 * 24 * 60 * 60; // 7日間（秒）
    private const IMAGE_CACHE_DIR = 'pokemon_cache/images';
    private const CRY_CACHE_DIR = 'pokemon_cache/cries';
    private const DISK = 'public'; // publicディスクを使用
    
    /**
     * ポケモン画像をキャッシュから取得または外部から取得してキャッシュ
     */
    public function getCachedPokemonImage(int $pokemonId): string
    {
        $filename = "{$pokemonId}.png";
        $cachePath = self::IMAGE_CACHE_DIR . '/' . $filename;
        
        // キャッシュが存在し、期限内かチェック
        if ($this->isCacheValid($cachePath)) {
            return $this->getCachedFileUrl($cachePath);
        }
        
        // 外部から取得してキャッシュ
        $imageUrl = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$pokemonId}.png";
        
        try {
            $response = Http::timeout(30)->get($imageUrl);
            
            if ($response->successful()) {
                // ファイルをキャッシュディレクトリに保存
                Storage::disk(self::DISK)->put($cachePath, $response->body());
                Log::info("ポケモン画像をキャッシュしました: Pokemon ID {$pokemonId}");
                
                return $this->getCachedFileUrl($cachePath);
            }
        } catch (\Exception $e) {
            Log::error("ポケモン画像の取得に失敗: Pokemon ID {$pokemonId}", [
                'error' => $e->getMessage()
            ]);
        }
        
        // 取得に失敗した場合は元のURLを返す
        return $imageUrl;
    }
    
    /**
     * ポケモン鳴き声をキャッシュから取得または外部から取得してキャッシュ
     */
    public function getCachedPokemonCry(int $pokemonId): string
    {
        $filename = "{$pokemonId}.ogg";
        $cachePath = self::CRY_CACHE_DIR . '/' . $filename;
        
        // キャッシュが存在し、期限内かチェック
        if ($this->isCacheValid($cachePath)) {
            return $this->getCachedFileUrl($cachePath);
        }
        
        // 外部から取得してキャッシュ
        $cryUrl = "https://raw.githubusercontent.com/PokeAPI/cries/main/cries/pokemon/latest/{$pokemonId}.ogg";
        
        try {
            $response = Http::timeout(30)->get($cryUrl);
            
            if ($response->successful()) {
                // ファイルをキャッシュディレクトリに保存
                Storage::disk(self::DISK)->put($cachePath, $response->body());
                Log::info("ポケモン鳴き声をキャッシュしました: Pokemon ID {$pokemonId}");
                
                return $this->getCachedFileUrl($cachePath);
            }
        } catch (\Exception $e) {
            Log::error("ポケモン鳴き声の取得に失敗: Pokemon ID {$pokemonId}", [
                'error' => $e->getMessage()
            ]);
        }
        
        // 取得に失敗した場合は元のURLを返す
        return $cryUrl;
    }
    
    /**
     * キャッシュファイルが有効（存在し、期限内）かチェック
     */
    private function isCacheValid(string $cachePath): bool
    {
        if (!Storage::disk(self::DISK)->exists($cachePath)) {
            return false;
        }
        
        $lastModified = Storage::disk(self::DISK)->lastModified($cachePath);
        return (time() - $lastModified) < self::CACHE_DURATION;
    }
    
    /**
     * キャッシュファイルのURLを取得
     */
    private function getCachedFileUrl(string $cachePath): string
    {
        return Storage::disk(self::DISK)->url($cachePath);
    }
    
    /**
     * 期限切れキャッシュをクリーンアップ
     */
    public function cleanExpiredCache(): void
    {
        $this->cleanDirectory(self::IMAGE_CACHE_DIR);
        $this->cleanDirectory(self::CRY_CACHE_DIR);
    }
    
    /**
     * 指定ディレクトリの期限切れファイルを削除
     */
    private function cleanDirectory(string $directory): void
    {
        $files = Storage::disk(self::DISK)->files($directory);
        $deletedCount = 0;
        
        foreach ($files as $file) {
            $lastModified = Storage::disk(self::DISK)->lastModified($file);
            if ((time() - $lastModified) >= self::CACHE_DURATION) {
                Storage::disk(self::DISK)->delete($file);
                $deletedCount++;
            }
        }
        
        if ($deletedCount > 0) {
            Log::info("期限切れキャッシュを削除しました: {$directory} ({$deletedCount}ファイル)");
        }
    }
    
    /**
     * キャッシュ統計情報を取得
     */
    public function getCacheStats(): array
    {
        $imageFiles = Storage::disk(self::DISK)->files(self::IMAGE_CACHE_DIR);
        $cryFiles = Storage::disk(self::DISK)->files(self::CRY_CACHE_DIR);
        
        $imageSize = 0;
        foreach ($imageFiles as $file) {
            $imageSize += Storage::disk(self::DISK)->size($file);
        }
        
        $crySize = 0;
        foreach ($cryFiles as $file) {
            $crySize += Storage::disk(self::DISK)->size($file);
        }
        
        return [
            'image_files' => count($imageFiles),
            'cry_files' => count($cryFiles),
            'total_files' => count($imageFiles) + count($cryFiles),
            'image_size' => $imageSize,
            'cry_size' => $crySize,
            'total_size' => $imageSize + $crySize,
            'cache_duration_days' => self::CACHE_DURATION / (24 * 60 * 60),
        ];
    }
}