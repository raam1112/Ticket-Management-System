<?php

namespace Database\Factories;

use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TicketCategory>
 */
class TicketCategoryFactory extends Factory
{
    protected $model = TicketCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        return [
            'name'        => ucwords($name),
            'slug'        => Str::slug($name),
            'description' => fake()->sentence(),
            'icon'        => 'fa-tag',
            'color'       => '#6c757d',
            'is_active'   => true,
            'sort_order'  => fake()->numberBetween(1, 10),
        ];
    }
}
