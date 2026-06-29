<?php

namespace Database\Factories;

use App\Models\SlaPolicy;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SlaPolicy>
 */
class SlaPolicyFactory extends Factory
{
    protected $model = SlaPolicy::class;

    public function definition(): array
    {
        return [
            'name'                   => fake()->words(3, true),
            'category_id'            => null,
            'priority_id'            => null,
            'response_time_hours'    => fake()->numberBetween(1, 4),
            'resolution_time_hours'  => fake()->numberBetween(4, 48),
            'escalation_after_hours' => fake()->numberBetween(2, 24),
            'business_hours_only'    => false,
            'is_active'              => true,
        ];
    }
}
