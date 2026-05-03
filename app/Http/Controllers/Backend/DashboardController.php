<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Services\Charts\IncomeChartService;
use App\Services\Charts\PostChartService;
use App\Services\Charts\UserChartService;
use App\Services\LanguageService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserChartService $userChartService,
        private readonly LanguageService $languageService,
        private readonly PostChartService $postChartService,
        private readonly IncomeChartService $incomeChartService
    ) {
    }

    public function index()
    {
        $this->authorize('viewDashboard', User::class);

        $listingCounts = Product::selectRaw("
            COUNT(*) as total,
            SUM(status = 'approved') as active,
            SUM(status = 'sold') as sold,
            SUM(status = 'rejected') as rejected
        ")->first();

        return view(
            'backend.pages.dashboard.index',
            [
                'total_users' => number_format(User::count()),
                'active_listings' => number_format((int) $listingCounts->active),
                'sold_listings' => number_format((int) $listingCounts->sold),
                'rejected_listings' => number_format((int) $listingCounts->rejected),
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
                'income_data' => $this->incomeChartService->getIncomeData(
                    request()->get('income_filter_period', 'last_6_months')
                ),
                'order_source_data' => $this->incomeChartService->getOrderSourceData(
                    request()->get('income_filter_period', 'last_6_months')
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
