<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Stamp;
use App\Models\Pokemon;
use App\Models\StampType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StampControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stamps_index_displays_correctly(): void
    {
        $child = Child::factory()->create(['name' => 'テスト太郎']);
        $pokemon = Pokemon::factory()->create(['name' => 'ピカチュウ']);
        $stampType = StampType::factory()->create(['name' => '頑張ったスタンプ']);
        
        $stamp = Stamp::factory()->create([
            'child_id' => $child->id,
            'pokemon_id' => $pokemon->id,
            'stamp_type_id' => $stampType->id,
            'comment' => 'テストコメント',
        ]);

        $response = $this->get("/children/{$child->id}/stamps");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Stamps/Index')
                ->where('child.name', 'テスト太郎')
                ->has('stamps.data', 1)
                ->where('stamps.data.0.comment', 'テストコメント')
        );
    }

    public function test_stamps_create_displays_correctly(): void
    {
        $child = Child::factory()->create(['name' => 'テスト太郎']);

        $response = $this->get("/children/{$child->id}/stamps/create");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Stamps/Create')
                ->where('child.name', 'テスト太郎')
        );
    }

    public function test_can_store_stamp(): void
    {
        $child = Child::factory()->create(['name' => 'テスト太郎']);
        Pokemon::factory()->create(); // Need at least one pokemon for random selection
        StampType::factory()->create(); // Need at least one stamp type

        $stampData = [
            'comment' => '今日は宿題を頑張りました',
        ];

        $response = $this->post("/children/{$child->id}/stamps", $stampData);

        $response->assertRedirect("/children/{$child->id}/stamps");
        $response->assertSessionHas('success', 'スタンプを付けました！');

        $this->assertDatabaseHas('stamps', [
            'child_id' => $child->id,
            'comment' => '今日は宿題を頑張りました',
        ]);
    }

    public function test_stamps_show_displays_correctly(): void
    {
        $child = Child::factory()->create(['name' => 'テスト太郎']);
        $pokemon = Pokemon::factory()->create(['name' => 'ピカチュウ']);
        $stampType = StampType::factory()->create(['name' => '頑張ったスタンプ']);
        
        $stamp = Stamp::factory()->create([
            'child_id' => $child->id,
            'pokemon_id' => $pokemon->id,
            'stamp_type_id' => $stampType->id,
            'comment' => 'テストコメント',
        ]);

        $response = $this->get("/children/{$child->id}/stamps/{$stamp->id}");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Stamps/Show')
                ->where('child.name', 'テスト太郎')
                ->where('stamp.data.comment', 'テストコメント')
        );
    }

    public function test_stamps_show_returns_404_for_wrong_child(): void
    {
        $child1 = Child::factory()->create();
        $child2 = Child::factory()->create();
        $pokemon = Pokemon::factory()->create();
        $stampType = StampType::factory()->create();
        
        $stamp = Stamp::factory()->create([
            'child_id' => $child2->id,
            'pokemon_id' => $pokemon->id,
            'stamp_type_id' => $stampType->id,
        ]);

        $response = $this->get("/children/{$child1->id}/stamps/{$stamp->id}");

        $response->assertNotFound();
    }
}