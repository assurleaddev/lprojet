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
        'carrier',
        'tracking_code',
        'received_at',
        'wants_verification',
        'verification_fee',
        'source',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'wants_verification' => 'boolean',
        'verification_fee' => 'decimal:2',
    ];

    /**
     * The net amount the vendor will actually receive.
     */
    public function getPayoutAmountAttribute()
    {
        return $this->amount - ($this->platform_commission ?? 0);
    }

    /**
     * True platform gain: protection + verification + commission. Shipping goes to the delivery company.
     */
    public function getPlatformFeesAttribute()
    {
        return ($this->buyer_protection_fee ?? 0)
            + ($this->verification_fee ?? 0)
            + ($this->platform_commission ?? 0);
    }

    protected static function booted(): void
    {
        // Increment orders_count when a non-cancelled order is created
        static::created(function (Order $order) {
            if ($order->status !== 'cancelled' && $order->product_id) {
                \Illuminate\Support\Facades\DB::table('products')
                    ->where('id', $order->product_id)
                    ->increment('orders_count');
            }
        });

        // Handle cancellations: decrement if order transitions to cancelled
        static::updated(function (Order $order) {
            if ($order->wasChanged('status') && $order->status === 'cancelled' && $order->product_id) {
                \Illuminate\Support\Facades\DB::table('products')
                    ->where('id', $order->product_id)
                    ->decrement('orders_count');
            }
        });
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
