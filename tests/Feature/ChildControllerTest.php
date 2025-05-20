<?php

namespace Tests\Feature;

use App\Models\Child;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_children_index_displays_correctly(): void
    {
        $child1 = Child::factory()->create(['name' => 'テスト太郎', 'age' => 5]);
        $child2 = Child::factory()->create(['name' => 'テスト花子', 'age' => 7]);

        $response = $this->get('/children');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Children/Index')
                ->has('children.data', 2)
                ->where('children.data.0.name', 'テスト太郎')  // order by name alphabetically
                ->where('children.data.1.name', 'テスト花子')
        );
    }

    public function test_children_create_displays_correctly(): void
    {
        $response = $this->get('/children/create');

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page->component('Children/Create')
        );
    }

    public function test_can_store_child(): void
    {
        $childData = [
            'name' => '新しい太郎',
            'age' => 6,
        ];

        $response = $this->post('/children', $childData);

        $response->assertRedirect('/children');
        $response->assertSessionHas('success', '新しい太郎を登録しました');

        $this->assertDatabaseHas('children', $childData);
    }

    public function test_store_child_validation_fails_with_invalid_data(): void
    {
        $response = $this->post('/children', [
            'name' => '', // 空の名前
            'age' => 0,   // 無効な年齢
        ]);

        $response->assertSessionHasErrors(['name', 'age']);
    }

    public function test_children_show_displays_correctly(): void
    {
        $child = Child::factory()->create(['name' => 'テスト太郎', 'age' => 5]);

        $response = $this->get("/children/{$child->id}");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Children/Show')
                ->where('child.data.name', 'テスト太郎')
                ->where('child.data.age', 5)
                ->has('todayStampsCount')
                ->has('thisMonthStampsCount')
        );
    }

    public function test_children_edit_displays_correctly(): void
    {
        $child = Child::factory()->create(['name' => 'テスト太郎', 'age' => 5]);

        $response = $this->get("/children/{$child->id}/edit");

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Children/Edit')
                ->where('child.data.name', 'テスト太郎')
                ->where('child.data.age', 5)
        );
    }

    public function test_can_update_child(): void
    {
        $child = Child::factory()->create(['name' => '元の名前', 'age' => 5]);

        $updatedData = [
            'name' => '更新された名前',
            'age' => 6,
        ];

        $response = $this->put("/children/{$child->id}", $updatedData);

        $response->assertRedirect("/children/{$child->id}");
        $response->assertSessionHas('success', '更新された名前の情報を更新しました');

        $child->refresh();
        $this->assertEquals('更新された名前', $child->name);
        $this->assertEquals(6, $child->age);
    }

    public function test_can_delete_child(): void
    {
        $child = Child::factory()->create(['name' => '削除される太郎']);

        $response = $this->delete("/children/{$child->id}");

        $response->assertRedirect('/children');
        $response->assertSessionHas('success', '削除される太郎を削除しました');

        $this->assertDatabaseMissing('children', ['id' => $child->id]);
    }
}