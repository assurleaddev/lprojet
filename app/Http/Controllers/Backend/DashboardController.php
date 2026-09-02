<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Live;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Charts\PostChartService;
use App\Services\Charts\UserChartService;
use App\Services\LanguageService;
use Modules\Wallet\Models\WithdrawalRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserChartService $userChartService,
        private readonly LanguageService $languageService,
        private readonly PostChartService $postChartService,
    ) {
    }

    public function index()
    {
        $this->authorize('viewDashboard', User::class);

        // NB: 'rejected' is not a real product status (enum: pending/approved/
        // sold/reserved/hidden/holiday) — 'pending' is the actual moderation queue.
        $listingCounts = Product::selectRaw("
            COUNT(*) as total,
            SUM(status = 'approved') as active,
            SUM(status = 'sold') as sold,
            SUM(status = 'pending') as pending
        ")->first();

        return view(
            'backend.pages.dashboard.index',
            [
                'total_users' => number_format(User::count()),
                'active_listings' => number_format((int) $listingCounts->active),
                'sold_listings' => number_format((int) $listingCounts->sold),
                'pending_listings' => number_format((int) $listingCounts->pending),
                // Operations row: the queues an operator clears daily.
                'orders_today' => number_format(Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->count()),
                'gmv_today' => number_format((float) Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total_amount'), 2),
                'open_claims' => number_format(Claim::whereIn('status', ['pending', 'under_review'])->count()),
                'pending_withdrawals' => number_format(WithdrawalRequest::where('status', 'pending')->count()),
                'live_now' => number_format(Live::where('status', 'live')->count()),
                'total_roles' => number_format(Role::count()),
                'total_permissions' => number_format(Permission::count()),
                'languages' => [
                    'total' => number_format(count($this->languageService->getLanguages())),
                    'active' => number_format(count($this->languageService->getActiveLanguages())),
                ],
                'user_growth_data' => $this->userChartService->getUserGrowthData(
                    request()->get('chart_filter_period', 'last_6_months')
                )->getData(true),
                'user_history_data' => $this->userChartService->getUserHistoryData(),
                'post_stats' => $this->postChartService->getPostActivityData(
                    request()->get('post_chart_filter_period', 'last_6_months')
                ),
                'breadcrumbs' => [
                    'title' => __('Dashboard'),
                    'show_home' => false,
                    'show_current' => false,
                ],
            ]
        );
    }
}
