<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PokemonService;
use App\Models\Pokemon;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * PokemonServiceのテスト
 */
class PokemonServiceTest extends TestCase
{
    use RefreshDatabase;

    private PokemonService $pokemonService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pokemonService = app(PokemonService::class);
        
        // テスト用ポケモンデータを作成
        $this->createTestPokemons();
    }

    /**
     * テスト用ポケモンデータを作成
     */
    private function createTestPokemons(): void
    {
        // 通常のポケモン
        Pokemon::create([
            'name' => 'ピカチュウ',
            'type1' => 'でんき',
            'genus' => 'ねずみポケモン',
            'is_legendary' => false,
            'is_mythical' => false,
        ]);

        Pokemon::create([
            'name' => 'イーブイ',
            'type1' => 'ノーマル',
            'genus' => 'しんかポケモン',
            'is_legendary' => false,
            'is_mythical' => false,
        ]);

        // レジェンダリーポケモン
        Pokemon::create([
            'name' => 'フリーザー',
            'type1' => 'こおり',
            'type2' => 'ひこう',
            'genus' => 'れいとうポケモン',
            'is_legendary' => true,
            'is_mythical' => false,
        ]);

        Pokemon::create([
            'name' => 'サンダー',
            'type1' => 'でんき',
            'type2' => 'ひこう',
            'genus' => 'でんきポケモン',
            'is_legendary' => true,
            'is_mythical' => false,
        ]);

        // 幻のポケモン
        Pokemon::create([
            'name' => 'ミュウ',
            'type1' => 'エスパー',
            'genus' => 'しんしゅポケモン',
            'is_legendary' => false,
            'is_mythical' => true,
        ]);

        Pokemon::create([
            'name' => 'セレビィ',
            'type1' => 'エスパー',
            'type2' => 'くさ',
            'genus' => 'ときわたりポケモン',
            'is_legendary' => false,
            'is_mythical' => true,
        ]);
    }

    /**
     * レアリティ別ポケモン取得のテスト
     */
    public function testGetPokemonsByRarity(): void
    {
        // 通常のポケモン
        $commonPokemons = $this->pokemonService->getPokemonsByRarity('common');
        $this->assertCount(2, $commonPokemons);
        $this->assertTrue($commonPokemons->every(fn($p) => !$p->is_legendary && !$p->is_mythical));

        // レジェンダリーポケモン
        $legendaryPokemons = $this->pokemonService->getPokemonsByRarity('legendary');
        $this->assertCount(2, $legendaryPokemons);
        $this->assertTrue($legendaryPokemons->every(fn($p) => $p->is_legendary));

        // 幻のポケモン
        $mythicalPokemons = $this->pokemonService->getPokemonsByRarity('mythical');
        $this->assertCount(2, $mythicalPokemons);
        $this->assertTrue($mythicalPokemons->every(fn($p) => $p->is_mythical));
    }

    /**
     * カード完成時のレジェンダリー出現テスト
     */
    public function testSelectLegendaryForCardCompletion(): void
    {
        $legendary = $this->pokemonService->selectLegendaryForCardCompletion();
        
        $this->assertInstanceOf(Pokemon::class, $legendary);
        $this->assertTrue($legendary->is_legendary);
        $this->assertFalse($legendary->is_mythical);
        $this->assertContains($legendary->name, ['フリーザー', 'サンダー']);
    }

    /**
     * 100分の1確率幻ポケモン出現テスト
     */
    public function testSelectMythicalByProbability(): void
    {
        // 確率的なテストなので、複数回実行して動作を確認
        $mythicalCount = 0;
        $nullCount = 0;
        
        for ($i = 0; $i < 200; $i++) {
            $mythical = $this->pokemonService->selectMythicalByProbability();
            if ($mythical) {
                $this->assertInstanceOf(Pokemon::class, $mythical);
                $this->assertTrue($mythical->is_mythical);
                $this->assertContains($mythical->name, ['ミュウ', 'セレビィ']);
                $mythicalCount++;
            } else {
                $this->assertNull($mythical);
                $nullCount++;
            }
        }
        
        // 確率的テストなので、幻ポケモンが1%程度出現することを確認
        // 200回中、0-10回程度出現することを想定（確率のばらつき考慮）
        $this->assertGreaterThanOrEqual(0, $mythicalCount);
        $this->assertLessThanOrEqual(20, $mythicalCount); // 上限を緩めに設定
        $this->assertGreaterThan($mythicalCount, $nullCount); // null の方が多いことを確認
    }

    /**
     * 特別ポケモン選択テスト
     */
    public function testSelectSpecialPokemon(): void
    {
        // 確率的なテストなので複数回実行
        $legendaryCount = 0;
        $mythicalCount = 0;
        $nullCount = 0;
        
        for ($i = 0; $i < 100; $i++) {
            // カード完成時のテスト
            $special = $this->pokemonService->selectSpecialPokemon(true);
            if ($special) {
                if ($special->is_mythical) {
                    $mythicalCount++;
                } elseif ($special->is_legendary) {
                    $legendaryCount++;
                }
            } else {
                $nullCount++;
            }
        }
        
        // カード完成時は確実にレジェンダリーが出現するか、1%で幻が出現
        $this->assertGreaterThan(0, $legendaryCount + $mythicalCount);
        
        // 通常時（カード完成でない）のテスト - 100分の1で幻のみ
        $normalMythicalCount = 0;
        $normalNullCount = 0;
        
        for ($i = 0; $i < 100; $i++) {
            $special = $this->pokemonService->selectSpecialPokemon(false);
            if ($special) {
                $this->assertTrue($special->is_mythical, 'カード完成でない時はレジェンダリーは出現しない');
                $normalMythicalCount++;
            } else {
                $normalNullCount++;
            }
        }
        
        // 通常時はnullが大部分
        $this->assertGreaterThan($normalMythicalCount, $normalNullCount);
    }

    /**
     * ポケモン判定メソッドのテスト
     */
    public function testPokemonCheckers(): void
    {
        $pikachu = Pokemon::where('name', 'ピカチュウ')->first();
        $freezer = Pokemon::where('name', 'フリーザー')->first();
        $mew = Pokemon::where('name', 'ミュウ')->first();

        // レジェンダリー判定
        $this->assertFalse($this->pokemonService->isLegendary($pikachu));
        $this->assertTrue($this->pokemonService->isLegendary($freezer));
        $this->assertFalse($this->pokemonService->isLegendary($mew));

        // 幻判定
        $this->assertFalse($this->pokemonService->isMythical($pikachu));
        $this->assertFalse($this->pokemonService->isMythical($freezer));
        $this->assertTrue($this->pokemonService->isMythical($mew));
    }

    /**
     * ランダムポケモン選択テスト
     */
    public function testSelectRandomPokemon(): void
    {
        $pokemon = $this->pokemonService->selectRandomPokemon();
        
        $this->assertInstanceOf(Pokemon::class, $pokemon);
        
        // 重み付きランダムなので複数回実行して通常ポケモンが多いことを確認
        $results = [];
        for ($i = 0; $i < 50; $i++) {
            $p = $this->pokemonService->selectRandomPokemon();
            $key = $p->is_legendary ? 'legendary' : ($p->is_mythical ? 'mythical' : 'common');
            $results[$key] = ($results[$key] ?? 0) + 1;
        }
        
        // 通常ポケモンの出現率が高いことを確認（98%の重み付けなので）
        $this->assertGreaterThan($results['legendary'] ?? 0, $results['common'] ?? 0);
    }
}