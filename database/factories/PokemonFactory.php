<?php

namespace Database\Factories;

use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pokemon>
 */
class PokemonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Pokemon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . 'ポケモン',
            'image_path' => fake()->slug() . '.png',
            'rarity' => fake()->randomElement(['common', 'rare', 'legendary']),
        ];
    }

    /**
     * コモンレアリティのポケモンを作成
     *
     * @return static
     */
    public function common(): static
    {
        return $this->state(fn (array $attributes) => [
            'rarity' => 'common',
        ]);
    }

    /**
     * レアレアリティのポケモンを作成
     *
     * @return static
     */
    public function rare(): static
    {
        return $this->state(fn (array $attributes) => [
            'rarity' => 'rare',
        ]);
    }

    /**
     * レジェンダリーレアリティのポケモンを作成
     *
     * @return static
     */
    public function legendary(): static
    {
        return $this->state(fn (array $attributes) => [
            'rarity' => 'legendary',
        ]);
    }
}
