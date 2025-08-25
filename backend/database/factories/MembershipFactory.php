<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 0, 500),
            'duration_days' => $this->faker->numberBetween(1, 90),
            'has_coach' => $this->faker->boolean(),
            'has_workout_plan' => $this->faker->boolean(),
            'has_nutrition_plan' => $this->faker->boolean(),
            'is_active' => true

        ];
    }
}
