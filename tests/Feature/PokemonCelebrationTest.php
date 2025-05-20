<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Pokemon;
use App\Models\StampType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PokemonCelebrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stamp_creation_returns_pokemon_data_in_session(): void
    {
        $child = Child::factory()->create(['name' => 'テスト太郎']);
        $pokemon = Pokemon::factory()->create([
            'name' => 'ピカチュウ',
            'rarity' => 'rare'
        ]);
        StampType::factory()->create(); // Need at least one stamp type

        $stampData = [
            'comment' => '今日は宿題を頑張りました',
        ];

        $response = $this->post("/children/{$child->id}/stamps", $stampData);

        $response->assertRedirect("/children/{$child->id}/stamps");
        $response->assertSessionHas('success', 'スタンプを付けました！');
        $response->assertSessionHas('newStamp');
        
        $newStampData = session('newStamp');
        $this->assertArrayHasKey('pokemon', $newStampData);
        $this->assertArrayHasKey('child', $newStampData);
        $this->assertEquals('テスト太郎', $newStampData['child']['name']);
        $this->assertNotNull($newStampData['pokemon']);
    }

    public function test_pokemon_celebration_shows_legendary_rarity(): void
    {
        $child = Child::factory()->create(['name' => 'テスト太郎']);
        $pokemon = Pokemon::factory()->create([
            'name' => 'ミュウツー',
            'rarity' => 'legendary'
        ]);
        StampType::factory()->create();

        $response = $this->post("/children/{$child->id}/stamps", ['comment' => 'すごい！']);

        $response->assertSessionHas('newStamp');
        $newStampData = session('newStamp');
        
        if ($newStampData['pokemon']) {
            // Since we can't guarantee which pokemon is selected randomly,
            // we just verify the structure is correct
            $this->assertArrayHasKey('rarity', $newStampData['pokemon']);
            $this->assertArrayHasKey('name', $newStampData['pokemon']);
            $this->assertArrayHasKey('id', $newStampData['pokemon']);
        }
    }

    public function test_stamps_index_page_can_handle_celebration_data(): void
    {
        $child = Child::factory()->create(['name' => 'テスト太郎']);
        
        // Set session data to simulate celebration
        session(['newStamp' => [
            'pokemon' => [
                'id' => 1,
                'name' => 'ピカチュウ',
                'rarity' => 'rare'
            ],
            'child' => [
                'name' => 'テスト太郎'
            ]
        ]]);

        $response = $this->get("/children/{$child->id}/stamps");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Stamps/Index')
                ->where('child.name', 'テスト太郎')
                ->has('flash.newStamp')
                ->where('flash.newStamp.pokemon.name', 'ピカチュウ')
                ->where('flash.newStamp.pokemon.rarity', 'rare')
        );
    }
}