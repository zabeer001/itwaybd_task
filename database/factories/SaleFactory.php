<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
   protected $model = \App\Models\Sale::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id, // Random user
            'total_amount' => 0, // Will calculate after attaching products
            'softdelete' => 0,
        ];
    }
}
