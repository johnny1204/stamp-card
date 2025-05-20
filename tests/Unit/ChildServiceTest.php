<?php

namespace Tests\Unit;

use App\Models\Child;
use App\Services\ChildService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChildService $childService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->childService = new ChildService();
    }

    public function test_create_child_with_valid_data(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'age' => 8,
            'avatar_path' => 'avatars/test.png'
        ];

        $child = $this->childService->createChild($data);

        $this->assertInstanceOf(Child::class, $child);
        $this->assertEquals('テスト太郎', $child->name);
        $this->assertEquals(8, $child->age);
        $this->assertEquals('avatars/test.png', $child->avatar_path);
        $this->assertDatabaseHas('children', $data);
    }

    public function test_create_child_with_minimal_data(): void
    {
        $data = ['name' => 'テスト花子'];

        $child = $this->childService->createChild($data);

        $this->assertInstanceOf(Child::class, $child);
        $this->assertEquals('テスト花子', $child->name);
        $this->assertNull($child->age);
        $this->assertNull($child->avatar_path);
    }

    public function test_get_all_children(): void
    {
        Child::factory()->count(3)->create();

        $children = $this->childService->getAllChildren();

        $this->assertCount(3, $children);
        $this->assertInstanceOf(Child::class, $children->first());
    }

    public function test_find_existing_child(): void
    {
        $child = Child::factory()->create();

        $foundChild = $this->childService->findChild($child->id);

        $this->assertInstanceOf(Child::class, $foundChild);
        $this->assertEquals($child->id, $foundChild->id);
    }

    public function test_find_non_existing_child(): void
    {
        $foundChild = $this->childService->findChild(999);

        $this->assertNull($foundChild);
    }

    public function test_update_child(): void
    {
        $child = Child::factory()->create([
            'name' => '元の名前',
            'age' => 5
        ]);

        $updateData = [
            'name' => '新しい名前',
            'age' => 6
        ];

        $updatedChild = $this->childService->updateChild($child, $updateData);

        $this->assertEquals('新しい名前', $updatedChild->name);
        $this->assertEquals(6, $updatedChild->age);
        $this->assertDatabaseHas('children', [
            'id' => $child->id,
            'name' => '新しい名前',
            'age' => 6
        ]);
    }

    public function test_delete_child(): void
    {
        $child = Child::factory()->create();

        $result = $this->childService->deleteChild($child);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('children', ['id' => $child->id]);
    }

    public function test_get_today_stamps_count(): void
    {
        $child = Child::factory()->create();
        
        // 今日のスタンプを2個作成（実際の実装ではStampFactory使用）
        $child->stamps()->create([
            'stamp_type_id' => 1,
            'pokemon_id' => 1,
            'stamped_at' => now(),
        ]);
        $child->stamps()->create([
            'stamp_type_id' => 2,
            'pokemon_id' => 2,
            'stamped_at' => now(),
        ]);

        $count = $this->childService->getTodayStampsCount($child);

        $this->assertEquals(2, $count);
    }
}
