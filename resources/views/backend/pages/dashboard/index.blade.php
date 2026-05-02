<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    @section('before_vite_build')
        <script>
            var userGrowthData = @json($user_growth_data['data']);
            var userGrowthLabels = @json($user_growth_data['labels']);
        </script>
    @endsection

    {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_AFTER_BREADCRUMBS, '') !!}

    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 space-y-6">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-4 md:gap-6">
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_BEFORE_USERS, '') !!}
                @include('backend.pages.dashboard.partials.card', [
                    "icon" => 'heroicons:user-group',
                    'icon_bg' => '#635BFF',
                    'label' => __('Users'),
                    'value' => $total_users,
                    'class' => 'bg-white',
                    'url' => route('admin.users.index'),
                    'enable_full_div_click' => true,
                ])
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER_USERS, '') !!}
                @include('backend.pages.dashboard.partials.card', [
                    'icon' => 'heroicons:key',
                    'icon_bg' => '#00D7FF',
                    'label' => __('Roles'),
                    'value' => $total_roles,
                    'class' => 'bg-white',
                    'url' => route('admin.roles.index'),
                    'enable_full_div_click' => true,
                ])
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER_ROLES, '') !!}
                @include('backend.pages.dashboard.partials.card', [
                    'icon' => 'bi:shield-check',
                    'icon_bg' => '#FF4D96',
                    'label' => __('Permissions'),
                    'value' => $total_permissions,
                    'class' => 'bg-white',
                    'url' => route('admin.permissions.index'),
                    'enable_full_div_click' => true,
                ])
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER_PERMISSIONS, '') !!}
                @include('backend.pages.dashboard.partials.card', [
                    'icon' => 'heroicons:language',
                    'icon_bg' => '#22C55E',
                    'label' => __('Translations'),
                    'value' => $languages['total'] . ' / ' . $languages['active'],
                    'class' => 'bg-white',
                    'url' => route('admin.translations.index'),
                    'enable_full_div_click' => true,
                ])
                {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER_TRANSLATIONS, '') !!}
            </div>
        </div>
    </div>

    {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_CARDS_AFTER, '') !!}

    {{-- Marketplace Metrics --}}
    <div class="mt-6">
        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('Marketplace') }}</h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 md:gap-6">
            @include('backend.pages.dashboard.partials.card', [
                'icon' => 'heroicons:user-group',
                'icon_bg' => '#635BFF',
                'label' => __('Total Users'),
                'value' => $total_users,
                'class' => 'bg-white',
                'url' => route('admin.users.index'),
                'enable_full_div_click' => true,
            ])
            @include('backend.pages.dashboard.partials.card', [
                'icon' => 'heroicons:tag',
                'icon_bg' => '#22C55E',
                'label' => __('Active Listings'),
                'value' => $active_listings,
                'class' => 'bg-white',
                'url' => route('admin.products.index', ['status' => 'approved']),
                'enable_full_div_click' => true,
            ])
            @include('backend.pages.dashboard.partials.card', [
                'icon' => 'heroicons:shopping-bag',
                'icon_bg' => '#F59E0B',
                'label' => __('Sold Listings'),
                'value' => $sold_listings,
                'class' => 'bg-white',
                'url' => route('admin.products.index', ['status' => 'sold']),
                'enable_full_div_click' => true,
            ])
            @include('backend.pages.dashboard.partials.card', [
                'icon' => 'heroicons:x-circle',
                'icon_bg' => '#EF4444',
                'label' => __('Rejected Listings'),
                'value' => $rejected_listings,
                'class' => 'bg-white',
                'url' => route('admin.products.index', ['status' => 'rejected']),
                'enable_full_div_click' => true,
            ])
        </div>
    </div>

    <div class="mt-6">
        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12">
                <div class="grid grid-cols-12 gap-4 md:gap-6">
                    <div class="col-span-12 md:col-span-8">
                        @include('backend.pages.dashboard.partials.user-growth')
                    </div>
                    <div class="col-span-12 md:col-span-4">
                        @include('backend.pages.dashboard.partials.user-history')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <div class="grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12">
                <div class="grid grid-cols-12 gap-4 md:gap-6">
                    @include('backend.pages.dashboard.partials.post-chart')
                </div>
            </div>
        </div>
    </div>

    {{-- Income Charts --}}
    <div class="mt-6">
        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">{{ __('Revenue') }}</h2>
        <div class="grid grid-cols-12 gap-4 md:gap-6">
            @include('backend.pages.dashboard.partials.income-chart')
        </div>
    </div>

    {!! Hook::applyFilters(DashboardFilterHook::DASHBOARD_AFTER, '') !!}
</x-layouts.backend-layout>