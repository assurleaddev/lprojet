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
        'shipping_cost',
        'buyer_protection_fee',
        'platform_commission',
        'total_amount',
        'status',
        'parcel_size',
        'delivery_receipt_path',
        'payment_method',
        'address_id',
        'shipping_option_id',
        'offer_id',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    /**
     * The net amount the vendor will actually receive.
     */
    public function getPayoutAmountAttribute()
    {
        return $this->amount - ($this->platform_commission ?? 0);
    }

    /**
     * Total fees collected by the platform for this order.
     */
    public function getPlatformFeesAttribute()
    {
        return ($this->total_amount - $this->amount) + ($this->platform_commission ?? 0);
    }

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

    // The delivery address for the order
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function shippingOption()
    {
        return $this->belongsTo(ShippingOption::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function offer()
    {
        return $this->belongsTo(\Modules\Chat\Models\Offer::class);
    }
}
