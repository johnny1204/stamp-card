<?php

namespace Database\Factories;

use App\Models\StampType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StampType>
 */
class StampTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = StampType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . 'スタンプ',
            'icon' => fake()->randomElement(['⭐', '🌟', '💯', '👍', '🎉', '🏆']),
            'color' => fake()->hexColor(),
            'category' => fake()->randomElement(['help', 'lifestyle', 'behavior', 'custom']),
            'is_custom' => fake()->boolean(20), // 20%の確率でカスタム
        ];
    }

    /**
     * お手伝い系スタンプを作成
     */
    public function help(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'help',
            'name' => fake()->randomElement(['お掃除', 'お料理手伝い', '片付け', 'お買い物']) . 'スタンプ',
            'icon' => fake()->randomElement(['🧹', '🍳', '📦', '🛒']),
        ]);
    }

    /**
     * 生活習慣系スタンプを作成
     */
    public function lifestyle(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'lifestyle',
            'name' => fake()->randomElement(['歯磨き', '早寝早起き', '宿題', '読書']) . 'スタンプ',
            'icon' => fake()->randomElement(['🦷', '🌅', '📚', '📖']),
        ]);
    }

    /**
     * 行動評価系スタンプを作成
     */
    public function behavior(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'behavior',
            'name' => fake()->randomElement(['優しさ', '頑張り', 'チャレンジ', '笑顔']) . 'スタンプ',
            'icon' => fake()->randomElement(['💖', '💪', '🚀', '😊']),
        ]);
    }

    /**
     * カスタムスタンプを作成
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'custom',
            'is_custom' => true,
        ]);
    }
}
