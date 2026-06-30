<?php

namespace Database\Factories;

use App\Enums\ProposalStatus;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

class TermProposalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'term_id' => Term::factory(),
            'email' => $this->faker->email(),
            'description' => $this->faker->paragraphs(2, asText: true),
            'status' => ProposalStatus::New,
        ];
    }

    /**
     * Create an approved proposal.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProposalStatus::Approved,
        ]);
    }

    /**
     * Create a rejected proposal.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProposalStatus::Rejected,
        ]);
    }

    /**
     * Create a proposal without email.
     */
    public function withoutEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
        ]);
    }
}
