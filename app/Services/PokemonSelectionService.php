<?php

namespace App\Services;

use App\Models\Pokemon;

/**
 * ポケモン選択サービス
 * 
 * レアリティに基づいた重み付きランダム選択を提供
 */
class PokemonSelectionService
{
    /**
     * レアリティ別の出現確率（％）
     */
    private const RARITY_WEIGHTS = [
        'normal' => 85,     // 通常ポケモン 85%
        'legendary' => 10,  // レジェンダリー 10%
        'mythical' => 5,    // 幻のポケモン 5%
    ];

    /**
     * 重み付きランダムでポケモンを選択
     *
     * @return Pokemon
     */
    public function selectRandomPokemon(): Pokemon
    {
        // デバッグモード: クエリパラメータでレアポケモンを強制指定
        if (app()->environment('local') && request()->has('legend')) {
            return $this->selectMythicalPokemon();
        }
        
        $random = mt_rand(1, 100);
        
        if ($random <= self::RARITY_WEIGHTS['mythical']) {
            // 5%の確率で幻のポケモン
            return $this->selectMythicalPokemon();
        } elseif ($random <= self::RARITY_WEIGHTS['mythical'] + self::RARITY_WEIGHTS['legendary']) {
            // 10%の確率でレジェンダリーポケモン
            return $this->selectLegendaryPokemon();
        } else {
            // 85%の確率で通常ポケモン
            return $this->selectNormalPokemon();
        }
    }

    /**
     * 通常ポケモンをランダム選択
     *
     * @return Pokemon
     */
    private function selectNormalPokemon(): Pokemon
    {
        return Pokemon::normal()->inRandomOrder()->first();
    }

    /**
     * レジェンダリーポケモンをランダム選択
     *
     * @return Pokemon
     */
    private function selectLegendaryPokemon(): Pokemon
    {
        return Pokemon::legendary()->inRandomOrder()->first();
    }

    /**
     * 幻のポケモンをランダム選択
     *
     * @return Pokemon
     */
    private function selectMythicalPokemon(): Pokemon
    {
        return Pokemon::mythical()->inRandomOrder()->first();
    }

    /**
     * ポケモンの画像URLを取得
     *
     * @param int $pokemonId
     * @return string
     */
    public function getPokemonImageUrl(int $pokemonId): string
    {
        return "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$pokemonId}.png";
    }

    /**
     * ポケモンの鳴き声URLを取得
     *
     * @param int $pokemonId
     * @return string
     */
    public function getPokemonCryUrl(int $pokemonId): string
    {
        return "https://raw.githubusercontent.com/PokeAPI/cries/main/cries/pokemon/latest/{$pokemonId}.ogg";
    }
}