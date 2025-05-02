<?php

namespace App\Http\Controllers;

use App\Services\PokemonCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * ポケモンメディア（画像・鳴き声）取得コントローラー
 */
class PokemonMediaController extends Controller
{
    public function __construct(
        private PokemonCacheService $pokemonCacheService
    ) {}

    /**
     * ポケモン画像を取得（キャッシュ優先）
     */
    public function getImage(Request $request, int $pokemonId)
    {
        try {
            $imageUrl = $this->pokemonCacheService->getCachedPokemonImage($pokemonId);
            
            return response()->json([
                'success' => true,
                'url' => $imageUrl,
                'pokemon_id' => $pokemonId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ポケモン画像の取得に失敗しました',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ポケモン鳴き声を取得（キャッシュ優先）
     */
    public function getCry(Request $request, int $pokemonId)
    {
        try {
            $cryUrl = $this->pokemonCacheService->getCachedPokemonCry($pokemonId);
            
            return response()->json([
                'success' => true,
                'url' => $cryUrl,
                'pokemon_id' => $pokemonId,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ポケモン鳴き声の取得に失敗しました',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * キャッシュ統計情報を取得
     */
    public function getCacheStats()
    {
        try {
            $stats = $this->pokemonCacheService->getCacheStats();
            
            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'キャッシュ統計の取得に失敗しました',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 期限切れキャッシュをクリーンアップ
     */
    public function cleanCache()
    {
        try {
            $this->pokemonCacheService->cleanExpiredCache();
            
            return response()->json([
                'success' => true,
                'message' => '期限切れキャッシュをクリーンアップしました',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'キャッシュクリーンアップに失敗しました',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}