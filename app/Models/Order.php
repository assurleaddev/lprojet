<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'vendor_id',
        'amount',
        'status',
        'delivery_receipt_path'
    ];

    // The customer who placed the order
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // The vendor who owns the product in the order
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    // The single product in the order
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
