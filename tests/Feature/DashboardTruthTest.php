<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Charts\IncomeChartService;
use App\Services\Charts\UserChartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function truthOrder(User $buyer, User $vendor, Product $product, string $status, float $commission = 10): Order
{
    return Order::create([
        'user_id' => $buyer->id,
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
        'amount' => 100,
        'shipping_cost' => 25,
        'buyer_protection_fee' => 5,
        'platform_commission' => $commission,
        'total_amount' => 130,
        'status' => $status,
        'payment_method' => 'wallet',
        'source' => 'direct',
    ]);
}

test('last_6_months yields exactly six monthly buckets', function () {
    $data = app(IncomeChartService::class)->getIncomeData('last_6_months');

    expect($data['labels'])->toHaveCount(6);
    expect(end($data['labels']))->toBe(now()->format('M Y'));
});

test('last_12_months yields exactly twelve monthly buckets', function () {
    $data = app(IncomeChartService::class)->getIncomeData('last_12_months');

    expect($data['labels'])->toHaveCount(12);
});

test('user growth uses daily buckets for last_7_days (carbon 3 float diff)', function () {
    // UserFactory randomises created_at over the past year — pin it to today.
    User::factory()->count(2)->create(['created_at' => now()]);

    $data = app(UserChartService::class)->getUserGrowthData('last_7_days')->getData(true);

    expect($data['labels'])->toHaveCount(7); // daily, not one monthly bucket
    expect(array_sum($data['data']))->toBe(2);
});

test('income and gmv exclude cancelled orders', function () {
    $vendor = User::factory()->create();
    $buyer = User::factory()->create();
    $cat = Category::create(['name' => 'C', 'slug' => 'truth-cat']);
    $product = Product::create([
        'name' => 'P', 'description' => 'd', 'price' => 100,
        'vendor_id' => $vendor->id, 'category_id' => $cat->id, 'status' => 'sold',
    ]);

    truthOrder($buyer, $vendor, $product, 'completed');
    truthOrder($buyer, $vendor, $product, 'cancelled');

    $svc = app(IncomeChartService::class);

    $global = $svc->getGlobalIncome('this_month');
    expect((float) $global['gross'])->toBe(130.0); // one order, not two
    expect($global['order_count'])->toBe(1);

    $income = $svc->getIncomeData('this_month');
    expect(array_sum($income['commission']))->toEqual(10.0);

    $sources = $svc->getOrderSourceData('this_month');
    expect($sources['direct'])->toBe(1);
});

test('dashboard exposes the pending-approval queue instead of a nonexistent rejected status', function () {
    $admin = User::factory()->create();
    // The admin group runs Spatie's role:Superadmin middleware (not Gate-based).
    \Spatie\Permission\Models\Role::findOrCreate('Superadmin', 'web');
    $admin->assignRole('Superadmin');
    $this->actingAs($admin);

    $cat = Category::create(['name' => 'C2', 'slug' => 'truth-cat-2']);
    Product::create([
        'name' => 'P1', 'description' => 'd', 'price' => 10,
        'vendor_id' => $admin->id, 'category_id' => $cat->id, 'status' => 'pending',
    ]);

    // Bypass the dashboard view policy (we test data, not authz).
    \Illuminate\Support\Facades\Gate::before(fn () => true);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Pending Approval')
        ->assertDontSee('Rejected Listings');
});

test('chat window paginates messages and can load older ones', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();
    $conv = \Modules\Chat\Models\Conversation::create([
        'user_one_id' => min($a->id, $b->id),
        'user_two_id' => max($a->id, $b->id),
    ]);
    foreach (range(1, 60) as $i) {
        $conv->messages()->create(['user_id' => $a->id, 'body' => "m{$i}"]);
    }

    $this->actingAs($b);
    $component = Livewire\Livewire::test(\Modules\Chat\Livewire\ChatWindow::class, ['conversationId' => $conv->id]);

    expect(count($component->get('messages')))->toBe(50);
    $component->assertSet('hasOlderMessages', true)
        ->assertSee('m60')      // newest is present
        ->assertDontSee('m1"')  // oldest is outside the window
        ->call('loadOlderMessages');

    expect(count($component->get('messages')))->toBe(60);
    $component->assertSet('hasOlderMessages', false);
});
