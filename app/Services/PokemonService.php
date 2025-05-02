<?php

namespace App\Services;

use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Collection;

/**
 * ポケモン関連のビジネスロジックを担当するサービス
 */
class PokemonService extends BaseService
{
    /** @var array<string, int> レアリティ別の出現率（パーセント） */
    private const RARITY_WEIGHTS = [
        'common' => 98,
        'legendary' => 2,
        // mythical は特別な条件でのみ出現（100回に1回）
    ];

    /**
     * 全てのポケモンを取得
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Pokemon>
     */
    public function getAllPokemons(): Collection
    {
        return Pokemon::orderBy('name')->get();
    }

    /**
     * レアリティに基づいてランダムにポケモンを選択する
     *
     * @return Pokemon 選択されたポケモン
     * @throws \Exception ポケモンが見つからない場合
     */
    public function selectRandomPokemon(): Pokemon
    {
        $rarity = $this->selectRarityByWeight();
        $pokemons = $this->getPokemonsByRarity($rarity);

        if ($pokemons->isEmpty()) {
            // フォールバック: 通常のポケモンを選択
            $pokemons = Pokemon::normal()->get();
            if ($pokemons->isEmpty()) {
                throw new \Exception('利用可能なポケモンが見つかりません');
            }
        }

        $randomIndex = rand(0, $pokemons->count() - 1);
        return $pokemons[$randomIndex];
    }

    /**
     * 指定したレアリティのポケモンを取得
     *
     * @param string $rarity レアリティ（common, legendary, mythical）
     * @return \Illuminate\Database\Eloquent\Collection<int, Pokemon>
     */
    public function getPokemonsByRarity(string $rarity): Collection
    {
        return match($rarity) {
            'legendary' => Pokemon::legendary()->get(),
            'mythical' => Pokemon::mythical()->get(),
            'common' => Pokemon::normal()->get(),
            default => Pokemon::normal()->get(),
        };
    }

    /**
     * ポケモンをIDで取得
     *
     * @param int $id ポケモンID
     * @return Pokemon|null ポケモン（見つからない場合null）
     */
    public function findPokemon(int $id): ?Pokemon
    {
        return Pokemon::find($id);
    }

    /**
     * 重み付きランダムでレアリティを選択
     *
     * @return string 選択されたレアリティ
     */
    private function selectRarityByWeight(): string
    {
        $random = rand(1, 100);
        $currentWeight = 0;

        foreach (self::RARITY_WEIGHTS as $rarity => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $rarity;
            }
        }

        // フォールバック（通常は発生しない）
        return 'common';
    }

    /**
     * レジェンダリーポケモンが出現したかチェック
     *
     * @param Pokemon $pokemon チェック対象のポケモン
     * @return bool レジェンダリーかどうか
     */
    public function isLegendary(Pokemon $pokemon): bool
    {
        return $pokemon->is_legendary;
    }

    /**
     * 幻のポケモンが出現したかチェック
     *
     * @param Pokemon $pokemon チェック対象のポケモン
     * @return bool 幻かどうか
     */
    public function isMythical(Pokemon $pokemon): bool
    {
        return $pokemon->is_mythical;
    }

    /**
     * スタンプカード完成時にレジェンダリーポケモンを選択
     * カードの最後に必ず1回出現
     *
     * @return Pokemon レジェンダリーポケモン
     * @throws \Exception レジェンダリーポケモンが見つからない場合
     */
    public function selectLegendaryForCardCompletion(): Pokemon
    {
        $legendaryPokemons = Pokemon::legendary()->get();
        
        if ($legendaryPokemons->isEmpty()) {
            throw new \Exception('レジェンダリーポケモンが見つかりません');
        }

        $randomIndex = rand(0, $legendaryPokemons->count() - 1);
        return $legendaryPokemons[$randomIndex];
    }

    /**
     * 100分の1の確率で幻のポケモンを選択
     * 
     * @return Pokemon|null 幻のポケモン（出現しない場合はnull）
     */
    public function selectMythicalByProbability(): ?Pokemon
    {
        // 100分の1（1%）の確率で幻ポケモン出現
        $random = rand(1, 100);
        if ($random === 1) {
            $mythicalPokemons = Pokemon::mythical()->get();
            
            if ($mythicalPokemons->isEmpty()) {
                return null;
            }

            $randomIndex = rand(0, $mythicalPokemons->count() - 1);
            return $mythicalPokemons[$randomIndex];
        }

        return null;
    }

    /**
     * 特別なレアポケモン選択（カード完成 または 確率的幻ポケモン）
     * 
     * @param bool $isCardCompletion カード完成かどうか
     * @return Pokemon|null 特別なポケモン（通常選択の場合はnull）
     */
    public function selectSpecialPokemon(bool $isCardCompletion): ?Pokemon
    {
        // 100分の1の確率で幻ポケモンチェック（カード完成より優先）
        $mythical = $this->selectMythicalByProbability();
        if ($mythical) {
            return $mythical;
        }

        // カード完成時のレジェンダリー
        if ($isCardCompletion) {
            return $this->selectLegendaryForCardCompletion();
        }

        return null;
    }

    /**
     * ポケモンの画像URLを取得
     *
     * @param Pokemon $pokemon 対象のポケモン
     * @return string 画像URL
     */
    public function getPokemonImageUrl(Pokemon $pokemon): string
    {
        if (empty($pokemon->image_path)) {
            return '/images/pokemons/default.png';
        }

        return '/images/pokemons/' . $pokemon->image_path;
    }
}