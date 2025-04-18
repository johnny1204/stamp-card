<?php

namespace Database\Factories;

use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Child>
 */
class ChildFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Child::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'birth_date' => fake()->dateTimeBetween('-15 years', '-2 years')->format('Y-m-d'),
            'avatar_path' => fake()->optional()->filePath(),
            'target_stamps' => fake()->numberBetween(5, 20),
        ];
    }

    /**
     * 特定の年齢の子どもを作成
     *
     * @param int $age 年齢
     * @return static
     */
    public function withAge(int $age): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => now()->subYears($age)->subMonths(fake()->numberBetween(0, 11))->format('Y-m-d'),
        ]);
    }

    /**
     * 特定の誕生日の子どもを作成
     *
     * @param string $birthDate 誕生日 (Y-m-d形式)
     * @return static
     */
    public function withBirthDate(string $birthDate): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $birthDate,
        ]);
    }

    /**
     * アバターなしの子どもを作成
     *
     * @return static
     */
    public function withoutAvatar(): static
    {
        return $this->state(fn (array $attributes) => [
            'avatar_path' => null,
        ]);
    }
}
