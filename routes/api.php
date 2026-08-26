<?php

use App\Http\Controllers\Api\ActionLogController;
use App\Http\Controllers\Api\AiContentController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\Mobile\CategoryController as MobileCategoryController;
use App\Http\Controllers\Api\Mobile\CheckoutController as MobileCheckoutController;
use App\Http\Controllers\Api\Mobile\InboxController as MobileInboxController;
use App\Http\Controllers\Api\Mobile\LiveController as MobileLiveController;
use App\Http\Controllers\Api\Mobile\OfferOrderController as MobileOfferOrderController;
use App\Http\Controllers\Api\Mobile\ProductController as MobileProductController;
use App\Http\Controllers\Api\Mobile\ProfileController as MobileProfileController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TermController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Backend\Api\TermController as BackendTermController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API endpoints
Route::get('/translations/{lang}', function (string $lang) {
    $path = resource_path("lang/{$lang}.json");

    if (! file_exists($path)) {
        return response()->json(['error' => 'Language not found'], 404);
    }

    $translations = json_decode(file_get_contents($path), true);

    return response()->json($translations);
});

// Mobile API routes
Route::prefix('mobile')->group(function () {
    // Public: browse products and categories
    Route::get('/products', [MobileProductController::class, 'index']);
    Route::get('/products/{id}', [MobileProductController::class, 'show']);
    Route::get('/products/{id}/similar', [MobileProductController::class, 'similarProducts']);
    Route::get('/categories', [MobileCategoryController::class, 'index']);
    Route::get('/categories/{id}', [MobileCategoryController::class, 'show']);
    Route::get('/categories/{id}/attributes', [MobileCategoryController::class, 'attributes']);
    Route::get('/brands', [MobileProductController::class, 'brands']);
    Route::get('/vendors/{id}/products', [MobileProductController::class, 'vendorProducts']);

    // Authenticated: sell + inbox
    Route::middleware('auth:sanctum')->group(function () {
        // Profile & settings
        Route::get('/profile', [MobileProfileController::class, 'show']);
        Route::post('/profile', [MobileProfileController::class, 'update']);
        Route::post('/profile/password', [MobileProfileController::class, 'updatePassword']);
        Route::post('/profile/notifications', [MobileProfileController::class, 'updateNotifications']);

        Route::get('/my-products', [MobileProductController::class, 'mine']);
        Route::post('/products', [MobileProductController::class, 'store']);
        Route::post('/products/{id}', [MobileProductController::class, 'update']);
        Route::post('/products/{id}/report', [MobileProductController::class, 'report']);
        Route::post('/products/{id}/status', [MobileProductController::class, 'updateStatus']);
        Route::delete('/products/{id}', [MobileProductController::class, 'destroy']);
        Route::post('/conversations', [MobileInboxController::class, 'startConversation']);
        Route::get('/conversations', [MobileInboxController::class, 'conversations']);
        Route::get('/conversations/{id}/messages', [MobileInboxController::class, 'messages']);
        Route::post('/conversations/{id}/messages', [MobileInboxController::class, 'sendMessage']);
        Route::get('/notifications', [MobileInboxController::class, 'notifications']);
        Route::post('/notifications/read', [MobileInboxController::class, 'markNotificationsRead']);

        // Offer actions
        Route::post('/offers', [MobileOfferOrderController::class, 'createOffer']);
        Route::post('/offers/{id}/accept', [MobileOfferOrderController::class, 'acceptOffer']);
        Route::post('/offers/{id}/reject', [MobileOfferOrderController::class, 'rejectOffer']);
        Route::post('/offers/{id}/counter', [MobileOfferOrderController::class, 'counterOffer']);
        Route::post('/offers/{id}/withdraw', [MobileOfferOrderController::class, 'withdrawOffer']);

        // Checkout data (item, addresses, shipping, wallet) + address management
        Route::get('/checkout/init', [MobileCheckoutController::class, 'init']);
        Route::post('/addresses', [MobileCheckoutController::class, 'storeAddress']);
        Route::delete('/addresses/{id}', [MobileCheckoutController::class, 'destroyAddress']);

        // Checkout (accepted offer or direct buy) → order
        Route::post('/checkout', [MobileOfferOrderController::class, 'checkout']);

        // Live auctions — viewer
        Route::get('/lives', [MobileLiveController::class, 'index']);
        Route::get('/lives/{id}', [MobileLiveController::class, 'show']);
        Route::get('/lives/{id}/comments', [MobileLiveController::class, 'comments']);
        Route::get('/lives/{id}/agora-token', [MobileLiveController::class, 'agoraToken']);
        Route::post('/lives/{id}/bid', [MobileLiveController::class, 'placeBid']);
        Route::post('/lives/{id}/comment', [MobileLiveController::class, 'postComment']);
        Route::post('/lives/{id}/like', [MobileLiveController::class, 'toggleLike']);
        Route::post('/lives/{id}/pre-bid', [MobileLiveController::class, 'preBid']);
        Route::get('/live-balance', [MobileLiveController::class, 'balance']);
        Route::get('/live-config', [MobileLiveController::class, 'config']);

        // Live auctions — seller ("go live")
        Route::get('/live-products', [MobileLiveController::class, 'sellerProducts']);
        Route::post('/lives', [MobileLiveController::class, 'store']);
        Route::post('/lives/{id}/go-live', [MobileLiveController::class, 'goLive']);
        Route::post('/lives/{id}/end', [MobileLiveController::class, 'endLive']);
        Route::post('/lives/{id}/set-product', [MobileLiveController::class, 'setProduct']);
        Route::post('/lives/{id}/close-auction', [MobileLiveController::class, 'closeAuction']);

        // Order actions
        Route::post('/orders/{id}/ship', [MobileOfferOrderController::class, 'shipOrder']);
        Route::post('/orders/{id}/receive', [MobileOfferOrderController::class, 'receiveOrder']);
    });
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/revoke-all', [AuthController::class, 'revokeAll']);
        Route::post('/refresh', [AuthController::class, 'refresh']);

        // Email + phone verification (mobile)
        Route::post('/email/send', [VerificationController::class, 'sendEmail']);
        Route::post('/email/verify', [VerificationController::class, 'verifyEmail']);
        Route::post('/phone/send', [VerificationController::class, 'sendPhone']);
        Route::post('/phone/verify', [VerificationController::class, 'verifyPhone']);
    });
});

// Protected API routes
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // User management
    Route::apiResource('users', UserController::class);
    Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('api.users.bulk-delete');

    // Role management
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/delete/bulk-delete', [RoleController::class, 'bulkDelete'])->name('api.roles.bulk-delete');

    // Permission management
    Route::get('permissions', [PermissionController::class, 'index'])->name('api.permissions.index');
    Route::get('permissions/groups', [PermissionController::class, 'groups'])->name('api.permissions.groups');
    Route::get('permissions/{id}', [PermissionController::class, 'show'])->name('api.permissions.show');

    // Posts management (dynamic post types)
    Route::prefix('posts')->group(function () {
        Route::get('/{postType?}', [PostController::class, 'index'])->name('api.posts.index');
        Route::post('/{postType}', [PostController::class, 'store'])->name('api.posts.store');
        Route::get('/{postType}/{id}', [PostController::class, 'show'])->name('api.posts.show');
        Route::put('/{postType}/{id}', [PostController::class, 'update'])->name('api.posts.update');
        Route::delete('/{postType}/{id}', [PostController::class, 'destroy'])->name('api.posts.destroy');
        Route::post('/{postType}/bulk-delete', [PostController::class, 'bulkDelete'])->name('api.posts.bulk-delete');
    });

    // Terms management (Categories, Tags, etc.)
    Route::prefix('terms')->group(function () {
        Route::get('/{taxonomy}', [TermController::class, 'index'])->name('api.terms.index');
        Route::post('/{taxonomy}', [TermController::class, 'store'])->name('api.terms.store');
        Route::get('/{taxonomy}/{id}', [TermController::class, 'show'])->name('api.terms.show');
        Route::put('/{taxonomy}/{id}', [TermController::class, 'update'])->name('api.terms.update');
        Route::delete('/{taxonomy}/{id}', [TermController::class, 'destroy'])->name('api.terms.destroy');
        Route::post('/{taxonomy}/bulk-delete', [TermController::class, 'bulkDelete'])->name('api.terms.bulk-delete');
    });

    // Settings management
    Route::get('settings', [SettingController::class, 'index'])->name('api.settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('api.settings.update');
    Route::get('settings/{key}', [SettingController::class, 'show'])->name('api.settings.show');

    // Action logs
    Route::get('action-logs', [ActionLogController::class, 'index'])->name('api.action-logs.index');
    Route::get('action-logs/{id}', [ActionLogController::class, 'show'])->name('api.action-logs.show');

    // AI Content Generation
    Route::prefix('ai')->group(function () {
        Route::get('providers', [AiContentController::class, 'getProviders'])->name('api.ai.providers');
        Route::post('generate-content', [AiContentController::class, 'generateContent'])->name('api.ai.generate-content');
    });

    // Module management
    Route::get('modules', [ModuleController::class, 'index'])->name('api.modules.index');
    Route::get('modules/{name}', [ModuleController::class, 'show'])->name('api.modules.show');
    Route::patch('modules/{name}/toggle-status', [ModuleController::class, 'toggleStatus'])->name('api.modules.toggle-status');
    Route::delete('modules/{name}', [ModuleController::class, 'destroy'])->name('api.modules.destroy');
});

// Admin API routes (for backward compatibility with existing web-based API calls)
Route::middleware(['auth', 'web'])->prefix('admin')->name('admin.api.')->group(function () {
    // Terms API (existing)
    Route::post('/terms/{taxonomy}', [BackendTermController::class, 'store'])->name('terms.store');
    Route::put('/terms/{taxonomy}/{id}', [BackendTermController::class, 'update'])->name('terms.update');
    Route::delete('/terms/{taxonomy}/{id}', [BackendTermController::class, 'destroy'])->name('terms.destroy');
});
