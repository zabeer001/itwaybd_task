<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\Product;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 sales
        Sale::factory()->count(10)->create()->each(function ($sale) {
            // Attach 2-5 random products to each sale
            $products = Product::inRandomOrder()->take(rand(2, 5))->get();

            $total = 0;

            foreach ($products as $product) {
                $quantity = rand(1, 5);
                $unitPrice = $product->price;
                $lineTotal = $unitPrice * $quantity;

                $sale->products()->sync($product->id, [
                    'quantity' => $quantity,
                ]);

                $total += $lineTotal;
            }

            // Update the sale total_amount
        $sale->update(['total_amount' => $total]);
        });
    }
}
