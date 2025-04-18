<?php

namespace Database\Factories;

use App\Models\Stamp;
use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stamp>
 */
class StampFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Stamp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'stamp_type_id' => fake()->numberBetween(1, 10),
            'pokemon_id' => fake()->numberBetween(1, 50),
            'stamped_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'comment' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * 今日作成されたスタンプを生成
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'stamped_at' => fake()->dateTimeBetween(today(), 'now'),
        ]);
    }

    /**
     * 昨日作成されたスタンプを生成
     */
    public function yesterday(): static
    {
        return $this->state(fn (array $attributes) => [
            'stamped_at' => fake()->dateTimeBetween(yesterday(), today()),
        ]);
    }

    /**
     * 今月作成されたスタンプを生成
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'stamped_at' => fake()->dateTimeBetween(now()->startOfMonth(), 'now'),
        ]);
    }

    /**
     * コメント付きのスタンプを生成
     */
    public function withComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => fake()->sentence(),
        ]);
    }

    /**
     * 特定の子どものスタンプを生成
     */
    public function forChild(Child $child): static
    {
        return $this->state(fn (array $attributes) => [
            'child_id' => $child->id,
        ]);
    }
}
