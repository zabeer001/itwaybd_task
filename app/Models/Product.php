<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'price',
        'description',
        'softdelete',
    ];

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'product_sales')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
