<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true), // Random product name
            'price' => $this->faker->randomFloat(2, 10, 1000), // Price between 10 and 1000
            'description' => $this->faker->sentence(), // Optional description
            'softdelete' => 0, // default 0
        ];
    }
}
