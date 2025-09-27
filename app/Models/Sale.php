<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sale_date',
        'total_amount',
        'softdelete',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_sales')
            ->withPivot('quantity')
            ->withTimestamps();
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
