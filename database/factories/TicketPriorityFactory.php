<?php

namespace Database\Factories;

use App\Models\TicketPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketPriority>
 */
class TicketPriorityFactory extends Factory
{
    protected $model = TicketPriority::class;

    public function definition(): array
    {
        return [
            'name'               => fake()->unique()->word(),
            'display_name'       => fake()->words(2, true),
            'color'              => fake()->hexColor(),
            'icon'               => 'fa-circle',
            'sla_hours_response' => fake()->numberBetween(1, 8),
            'sla_hours_resolve'  => fake()->numberBetween(8, 72),
            'sort_order'         => fake()->numberBetween(1, 5),
            'is_active'          => true,
        ];
    }
}
