<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Amenity;

class AmenityFactory extends Factory
{
    protected $model = Amenity::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word,
            'slug' => $this->faker->unique()->slug,
            'icon' => 'fas fa-check',
            'category' => $this->faker->randomElement(['general', 'safety', 'kitchen', 'bathroom']),
        ];
    }
}
