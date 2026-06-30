<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SearchQueryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $searchQuery = $this->faker->words(2, asText: true);

        return [
            'search_query' => $searchQuery,
            'title' => null,
            'count' => $this->faker->numberBetween(1, 100),
            'searched_at' => now()->subDays($this->faker->numberBetween(0, 30)),
            'has_result' => $this->faker->boolean(80),
        ];
    }

    /**
     * Create a query with results.
     */
    public function withResults(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_result' => true,
        ]);
    }

    /**
     * Create a query without results.
     */
    public function withoutResults(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_result' => false,
        ]);
    }
}
