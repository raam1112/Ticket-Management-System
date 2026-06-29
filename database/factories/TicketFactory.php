<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(6),
            'description' => fake()->paragraph(3),
            'category_id' => TicketCategory::factory(),
            'priority_id' => TicketPriority::factory(),
            'created_by'  => User::factory(),
            'status'      => fake()->randomElement(['open', 'assigned', 'in_progress', 'resolved', 'closed']),
            'source'      => 'web',
            'is_internal' => false,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'open']);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => 'resolved',
            'resolved_at' => now(),
        ]);
    }
}
