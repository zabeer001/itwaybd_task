<?php

namespace App\Helpers;

use App\Models\Product;

class SaleHelper
{
    /**
     * Calculate total amount for a sale
     * 
     * @param array $products Each item: ['product_id' => int, 'quantity' => int]
     * @return float
     */
    public static function calculateTotalAmount(array $products): float
    {
        $total = 0;

        foreach ($products as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $total += $product->price * $item['quantity'];
            }
        }

        return $total;
    }
}
