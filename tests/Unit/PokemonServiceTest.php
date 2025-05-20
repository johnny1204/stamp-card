<?php

namespace Tests\Unit;

use App\Models\Pokemon;
use App\Services\PokemonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PokemonServiceTest extends TestCase
{
    use RefreshDatabase;

    private PokemonService $pokemonService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pokemonService = new PokemonService();
    }

    public function test_get_all_pokemons(): void
    {
        Pokemon::factory()->count(5)->create();

        $pokemons = $this->pokemonService->getAllPokemons();

        $this->assertCount(5, $pokemons);
        $this->assertInstanceOf(Pokemon::class, $pokemons->first());
    }

    public function test_find_existing_pokemon(): void
    {
        $pokemon = Pokemon::factory()->create();

        $foundPokemon = $this->pokemonService->findPokemon($pokemon->id);

        $this->assertInstanceOf(Pokemon::class, $foundPokemon);
        $this->assertEquals($pokemon->id, $foundPokemon->id);
    }

    public function test_find_non_existing_pokemon(): void
    {
        $foundPokemon = $this->pokemonService->findPokemon(999);

        $this->assertNull($foundPokemon);
    }

    public function test_get_pokemons_by_rarity(): void
    {
        Pokemon::factory()->common()->count(3)->create();
        Pokemon::factory()->rare()->count(2)->create();
        Pokemon::factory()->legendary()->count(1)->create();

        $commonPokemons = $this->pokemonService->getPokemonsByRarity('common');
        $rarePokemons = $this->pokemonService->getPokemonsByRarity('rare');
        $legendaryPokemons = $this->pokemonService->getPokemonsByRarity('legendary');

        $this->assertCount(3, $commonPokemons);
        $this->assertCount(2, $rarePokemons);
        $this->assertCount(1, $legendaryPokemons);
    }

    public function test_select_random_pokemon_returns_pokemon(): void
    {
        Pokemon::factory()->common()->count(5)->create();

        $pokemon = $this->pokemonService->selectRandomPokemon();

        $this->assertInstanceOf(Pokemon::class, $pokemon);
    }

    public function test_select_random_pokemon_throws_exception_when_no_pokemons(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('利用可能なポケモンが見つかりません');

        $this->pokemonService->selectRandomPokemon();
    }

    public function test_is_legendary(): void
    {
        $legendaryPokemon = Pokemon::factory()->legendary()->create();
        $commonPokemon = Pokemon::factory()->common()->create();

        $this->assertTrue($this->pokemonService->isLegendary($legendaryPokemon));
        $this->assertFalse($this->pokemonService->isLegendary($commonPokemon));
    }

    public function test_get_pokemon_image_url_with_valid_path(): void
    {
        $pokemon = Pokemon::factory()->create(['image_path' => 'pikachu.png']);

        $imageUrl = $this->pokemonService->getPokemonImageUrl($pokemon);

        $this->assertEquals('/images/pokemons/pikachu.png', $imageUrl);
    }

    public function test_get_pokemon_image_url_with_empty_path(): void
    {
        $pokemon = Pokemon::factory()->create(['image_path' => '']);

        $imageUrl = $this->pokemonService->getPokemonImageUrl($pokemon);

        $this->assertEquals('/images/pokemons/default.png', $imageUrl);
    }

    public function test_rarity_distribution_over_multiple_selections(): void
    {
        // 各レアリティのポケモンを作成
        Pokemon::factory()->common()->count(10)->create();
        Pokemon::factory()->rare()->count(5)->create();
        Pokemon::factory()->legendary()->count(2)->create();

        $selections = [];
        $numSelections = 100;

        // 100回選択して分布を確認
        for ($i = 0; $i < $numSelections; $i++) {
            $pokemon = $this->pokemonService->selectRandomPokemon();
            $selections[$pokemon->rarity] = ($selections[$pokemon->rarity] ?? 0) + 1;
        }

        // common が最も多く選ばれることを確認
        $this->assertGreaterThan($selections['rare'] ?? 0, $selections['common'] ?? 0);
        $this->assertGreaterThan($selections['legendary'] ?? 0, $selections['common'] ?? 0);
    }
}
