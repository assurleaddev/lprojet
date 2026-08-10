<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShippingOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Chat\Models\Offer;
use Modules\Wallet\Services\WalletService;

/**
 * Data for the mobile checkout screen (address / shipping / payment / summary),
 * mirroring the web checkout page. The order is created via the existing
 * POST /mobile/checkout (OfferOrderController@checkout).
 */
class CheckoutController extends Controller
{
    public function __construct(private readonly WalletService $wallet)
    {
    }

    public function init(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required_without:offer_id', 'nullable', 'exists:products,id'],
            'offer_id' => ['required_without:product_id', 'nullable', 'exists:chat_offers,id'],
        ]);

        $user = $request->user();

        if ($request->filled('offer_id')) {
            $offer = Offer::with(['product.media', 'product.brand'])->findOrFail($request->offer_id);
            if ($offer->buyer_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            $product = $offer->product;
            $amount = (float) $offer->offer_price;
        } else {
            $product = Product::with(['media', 'brand'])->findOrFail($request->product_id);
            if ((int) $product->vendor_id === $user->id) {
                return response()->json(['message' => 'Vous ne pouvez pas acheter votre propre article.'], 403);
            }
            if ($product->status !== 'approved') {
                return response()->json(['message' => 'Cet article n\'est plus disponible.'], 422);
            }
            $amount = (float) $product->price;
        }

        $shipping = ShippingOption::where('is_active', true)
            ->orderBy('type')
            ->orderBy('price')
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'key' => $o->key,
                'label' => $o->label,
                'type' => $o->type, // drop_off | home_pickup
                'price' => (float) $o->price,
                'description' => $o->description,
            ])->values();

        return response()->json([
            'item' => [
                'id' => $product?->id,
                'title' => $product?->name,
                'price' => $amount,
                'image' => $this->itemImage($product),
                'brand' => $product?->brand?->name,
                'size' => $product?->size,
            ],
            'addresses' => $user->addresses()->latest()->get()->map(fn ($a) => [
                'id' => $a->id,
                'full_name' => $a->full_name,
                'address_line_1' => $a->address_line_1,
                'address_line_2' => $a->address_line_2,
                'city' => $a->city,
                'postcode' => $a->postcode,
                'country' => $a->country,
            ])->values(),
            'shipping_options' => $shipping,
            'wallet_balance' => (float) ($this->wallet->getWallet($user)->balance ?? 0),
            'fees' => [
                'buyer_protection_percentage' => (float) config('settings.buyer_protection_fee_percentage', 5),
                'buyer_protection_fixed' => (float) config('settings.buyer_protection_fee_fixed', 0.70),
                'verification_fee' => (float) config('settings.product_verification_fee', 50),
                'verification_threshold' => (float) config('settings.product_verification_threshold', 500),
                'default_shipping' => (float) config('settings.delivery_fee_fixed', 25),
            ],
        ]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:255'],
        ]);

        $address = $request->user()->addresses()->create($validated);

        return response()->json([
            'id' => $address->id,
            'full_name' => $address->full_name,
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2,
            'city' => $address->city,
            'postcode' => $address->postcode,
            'country' => $address->country,
        ], 201);
    }

    public function destroyAddress(Request $request, int $id): JsonResponse
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json(['message' => 'Adresse supprimée']);
    }

    private function itemImage(?Product $product): ?string
    {
        if (! $product) {
            return null;
        }
        $media = $product->getFirstMedia('featured') ?: $product->getFirstMedia('products');
        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion('preview') ? $media->getUrl('preview') : $media->getUrl();
    }
}
