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
            'last_searched_at' => now()->subDays($this->faker->numberBetween(0, 30)),
            'last_ip' => $this->faker->ipv4(),
            'have_result' => $this->faker->boolean(80), // 80% have results
            'is_published' => false,
        ];
    }

    /**
     * Create a published search query.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    /**
     * Create a query with results.
     */
    public function withResults(): static
    {
        return $this->state(fn (array $attributes) => [
            'have_result' => true,
        ]);
    }

    /**
     * Create a query without results.
     */
    public function withoutResults(): static
    {
        return $this->state(fn (array $attributes) => [
            'have_result' => false,
        ]);
    }
}
