<?php

declare(strict_types=1);

use App\Http\Controllers\Backend\ActionLogController;
use App\Http\Controllers\Backend\Auth\ScreenshotGeneratorLoginController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\LocaleController;
use App\Http\Controllers\Backend\MediaController;
use App\Http\Controllers\Backend\ModuleController;
use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\PostController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\TermController;
use App\Http\Controllers\Backend\TranslationController;
use App\Http\Controllers\Backend\UserLoginAsController;
use App\Http\Controllers\Backend\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\Marketplace\ProductController;
use App\Http\Controllers\Backend\Marketplace\CategoryController;
use App\Http\Controllers\Backend\Marketplace\AttributeController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * User routes.
 */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/items/new', [App\Http\Controllers\ItemController::class, 'create'])->middleware('auth')->name('items.create');
Route::post('/items', [App\Http\Controllers\ItemController::class, 'store'])->middleware('auth')->name('items.store');
Route::get('/items/{product}/edit', [App\Http\Controllers\ItemController::class, 'edit'])->middleware('auth')->name('items.edit');
Route::put('/items/{product}', [App\Http\Controllers\ItemController::class, 'update'])->middleware('auth')->name('items.update');
Route::delete('/items/{product}', [App\Http\Controllers\ItemController::class, 'destroy'])->middleware('auth')->name('items.destroy');
Route::post('/items/{product}/mark-as-sold', [App\Http\Controllers\ItemController::class, 'markAsSold'])->middleware('auth')->name('items.markAsSold');
Route::post('/items/{product}/reserve', [App\Http\Controllers\ItemController::class, 'reserve'])->middleware('auth')->name('items.reserve');
Route::post('/items/{product}/unreserve', [App\Http\Controllers\ItemController::class, 'unreserve'])->middleware('auth')->name('items.unreserve');
Route::post('/items/{product}/hide', [App\Http\Controllers\ItemController::class, 'hide'])->middleware('auth')->name('items.hide');
Route::get('/items/categories/{category}/attributes', [App\Http\Controllers\ItemController::class, 'getAttributes'])->name('items.attributes');
Route::get('items/{product}', [HomeController::class, 'show'])->name('products.show');
Route::get('member/{user}', [HomeController::class, 'member_profile'])->name('vendor.show');
Route::post('/products/{product}/favorite', [HomeController::class, 'toggleFavorite'])
    ->middleware('auth')
    ->name('products.favorite');
Route::post('/users/{user}/follow', [HomeController::class, 'toggleFollow'])
    ->middleware('auth')
    ->name('users.follow.toggle');
Route::post('/users/{user}/block', [App\Http\Controllers\BlockUserController::class, 'toggleBlock'])
    ->middleware('auth')
    ->name('users.block.toggle');
Route::get('/users/{user}/followers', [HomeController::class, 'followers'])->name('users.followers');
Route::get('/users/{user}/following', [HomeController::class, 'following'])->name('users.following');
Route::get('/favorites', [HomeController::class, 'favorites'])->middleware('auth')->name('favorites.index');

Route::get('/product/{product}', [HomeController::class, 'checkout'])
    ->middleware('auth')
    ->name('product.checkout');

Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store')->middleware('auth');

// Verification Route
Route::get('/verify-email', \App\Livewire\Auth\VerifyEmail::class)
    ->middleware('auth')
    ->name('verify-email');
Route::get('/auth/secure-account', \App\Livewire\Auth\SecureAccountPrompt::class)
    ->middleware('auth')
    ->name('auth.secure_account');
Route::get('/auth/verify-phone', \App\Livewire\Auth\VerifyPhone::class)
    ->middleware('auth')
    ->name('auth.verify_phone');
Route::get('/auth/verify-phone-code', \App\Livewire\Auth\VerifyPhoneCode::class)
    ->middleware('auth')
    ->name('auth.verify_phone_code');

Route::get('/email/verify', function () {
    return redirect()->route('verify-email');
})->middleware('auth')->name('verification.notice');

// ⬇️ ADD THIS NEW ROUTE FOR OFFER CHECKOUT ⬇️
Route::get('/checkout/offer/{offer}', [HomeController::class, 'offerCheckout'])
    ->middleware('auth')
    ->name('checkout.offer');

Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search');

Route::get('/settings/profile', [App\Http\Controllers\SettingsController::class, 'profile'])
    ->middleware('auth')
    ->name('settings.profile');
Route::post('/settings/profile', [App\Http\Controllers\SettingsController::class, 'updateProfile'])
    ->middleware('auth')
    ->name('settings.profile.update');

Route::get('/settings/account', [App\Http\Controllers\SettingsController::class, 'account'])
    ->middleware('auth')
    ->name('settings.account');
Route::post('/settings/account', [App\Http\Controllers\SettingsController::class, 'updateAccount'])
    ->middleware('auth')
    ->name('settings.account.update');

Route::get('/settings/postage', [App\Http\Controllers\SettingsController::class, 'postage'])
    ->middleware('auth')
    ->name('settings.postage');
Route::post('/settings/postage', [App\Http\Controllers\SettingsController::class, 'updatePostage'])
    ->middleware('auth')
    ->name('settings.postage.update');
Route::post('/settings/address', [App\Http\Controllers\SettingsController::class, 'storeAddress'])
    ->middleware('auth')
    ->name('settings.address.store');

Route::get('/settings/notifications', [App\Http\Controllers\SettingsController::class, 'notifications'])
    ->middleware('auth')
    ->name('settings.notifications');
Route::post('/settings/notifications', [App\Http\Controllers\SettingsController::class, 'updateNotifications'])
    ->middleware('auth')
    ->name('settings.notifications.update');

Route::get('/settings/security', [App\Http\Controllers\SettingsController::class, 'security'])
    ->middleware('auth')
    ->name('settings.security');
Route::post('/settings/security/email/request', [App\Http\Controllers\SettingsController::class, 'requestEmailChange'])
    ->middleware('auth')
    ->name('settings.security.email.request');
Route::post('/settings/security/email/verify', [App\Http\Controllers\SettingsController::class, 'verifyEmailChange'])
    ->middleware('auth')
    ->name('settings.security.email.verify');
Route::post('/settings/security/password', [App\Http\Controllers\SettingsController::class, 'updatePassword'])
    ->middleware('auth')
    ->name('settings.security.password.update');
Route::post('/settings/security/2fa/toggle', [App\Http\Controllers\SettingsController::class, 'toggleTwoFactor'])
    ->middleware('auth')
    ->name('settings.security.2fa.toggle');
Route::post('/settings/security/2fa/verify', [App\Http\Controllers\SettingsController::class, 'verifyTwoFactor'])
    ->middleware('auth')
    ->name('settings.security.2fa.verify');
Route::post('/settings/security/session/{id}/logout', [App\Http\Controllers\SettingsController::class, 'logoutSession'])
    ->middleware('auth')
    ->name('settings.security.logout_session');
/**
 * Admin routes.
 */
Route::group(['prefix' => 'auth', 'as' => 'auth.social.'], function () {
    Route::get('/{provider}', [App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])->name('redirect');
    Route::get('/{provider}/callback', [App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])->name('callback');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('settings', SettingController::class);
    Route::resource('translations', TranslationController::class);
    Route::resource('roles', RoleController::class);
    Route::delete('roles/delete/bulk-delete', [RoleController::class, 'bulkDelete'])->name('roles.bulk-delete');

    // Permissions Routes.
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');

    // Modules Routes.
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    // Login as & Switch back.
    Route::resource('users', UserController::class);
    Route::delete('users/delete/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    Route::get('users/{id}/login-as', [UserLoginAsController::class, 'loginAs'])->name('users.login-as');
    Route::post('users/switch-back', [UserLoginAsController::class, 'switchBack'])->name('users.switch-back');

    // Action Log Routes.
    Route::get('/action-log', [ActionLogController::class, 'index'])->name('actionlog.index');

    // Posts/Pages Routes - Dynamic post types.
    Route::get('/posts/{postType?}', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{postType}/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts/{postType}', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{postType}/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{postType}/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{postType}/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{postType}/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::delete('/posts/{postType}/delete/bulk-delete', [PostController::class, 'bulkDelete'])->name('posts.bulk-delete');

    // Terms Routes (Categories, Tags, etc.).
    Route::get('/terms/{taxonomy}', [TermController::class, 'index'])->name('terms.index');
    Route::get('/terms/{taxonomy}/{term}/edit', [TermController::class, 'edit'])->name('terms.edit');
    Route::post('/terms/{taxonomy}', [TermController::class, 'store'])->name('terms.store');
    Route::put('/terms/{taxonomy}/{term}', [TermController::class, 'update'])->name('terms.update');
    Route::delete('/terms/{taxonomy}/{term}', [TermController::class, 'destroy'])->name('terms.destroy');
    Route::delete('/terms/{taxonomy}/delete/bulk-delete', [TermController::class, 'bulkDelete'])->name('terms.bulk-delete');

    // Media Routes.
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('/api', [MediaController::class, 'api'])->name('api');
        Route::post('/', [MediaController::class, 'store'])->name('store')->middleware('check.upload.limits');
        Route::get('/upload-limits', [MediaController::class, 'getUploadLimits'])->name('upload-limits');
        Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
        Route::delete('/', [MediaController::class, 'bulkDelete'])->name('bulk-delete');
    });

    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/approve', [ProductController::class, 'approve'])
        ->name('products.approve');

    Route::resource('orders', \App\Http\Controllers\Backend\Marketplace\OrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [\App\Http\Controllers\Backend\Marketplace\OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('orders/{order}/receipt', [\App\Http\Controllers\Backend\Marketplace\OrderController::class, 'uploadReceipt'])->name('orders.uploadReceipt');
    // Marketplace Routes
    Route::prefix('marketplace')->as('marketplace.')->group(function () {
        // Route::resource('products', ProductController::class);
        // Route::resource('categories', CategoryController::class);
        // Categories
        // Route::resource('categories', CategoryController::class)
        // ->names('backend.categories'); // This sets names like backend.categories.edit
        Route::resource('attributes', AttributeController::class);
        Route::get('categories/{category}/attributes', [ProductController::class, 'getAttributesByCategory'])->name('categories.attributes');
        Route::delete('product-images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
    });


    // Editor Upload Route.
    Route::post('/editor/upload', [App\Http\Controllers\Backend\EditorController::class, 'upload'])->name('editor.upload');

    // AI Content Generation Routes.
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/providers', [App\Http\Controllers\Backend\AiContentController::class, 'getProviders'])->name('providers');
        Route::post('/generate-content', [App\Http\Controllers\Backend\AiContentController::class, 'generateContent'])->name('generate-content');
        Route::post('/generate-content', [App\Http\Controllers\Backend\AiContentController::class, 'generateContent'])->name('generate-content');
    });

    Route::resource('shipping-options', \App\Http\Controllers\Backend\ShippingOptionController::class);
});

/**
 * Profile routes.
 */
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => ['auth']], function () {
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
    Route::put('/update-additional', [ProfileController::class, 'updateAdditional'])->name('update.additional');
});

Route::get('/locale/{lang}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/screenshot-login/{email}', [ScreenshotGeneratorLoginController::class, 'login'])->middleware('web')->name('screenshot.login');
Route::get('/demo-preview', fn() => view('demo.preview'))->name('demo.preview');
