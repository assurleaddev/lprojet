<?php

declare(strict_types=1);

namespace App\Livewire\Datatable;

use App\Enums\Hooks\UserActionHook;
use App\Enums\Hooks\UserFilterHook;
use App\Models\Role;
use App\Services\RolesService;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination; // Add if missing or just rely on parent
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class UserDatatable extends Datatable
{
    public string $role = '';
    public string $status = '';
    public bool $confirmingForceDelete = false;
    public array $usersWithDependencies = [];
    public array $usersToDeleteIds = [];
    public array $queryString = [
        ...parent::QUERY_STRING_DEFAULTS,
        'role' => [],
        'status' => [],
    ];
    public string $model = User::class;
    public array $disabledRoutes = ['view'];

    public function getSearchbarPlaceholder(): string
    {
        return __('Search by name or email...');
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function getFilters(): array
    {
        return [
            [
                'id' => 'role',
                'label' => __('Role'),
                'filterLabel' => __('Filter by Role'),
                'icon' => 'lucide:sliders',
                'allLabel' => __('All Roles'),
                'options' => app(RolesService::class)->getRolesDropdown(),
                'selected' => $this->role,
            ],
            [
                'id' => 'status',
                'label' => __('Status'),
                'filterLabel' => __('Filter by Status'),
                'icon' => 'lucide:filter',
                'allLabel' => __('All Statuses'),
                'options' => [
                    'active' => __('Active'),
                    'banned' => __('Banned'),
                ],
                'selected' => $this->status,
            ],
        ];
    }

    protected function getHeaders(): array
    {
        return [
            [
                'id' => 'name',
                'title' => __('Name'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'first_name',
            ],
            [
                'id' => 'email',
                'title' => __('Email'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'email',
            ],
            [
                'id' => 'roles',
                'title' => __('Roles'),
                'width' => null,
                'sortable' => false,
            ],
            [
                'id' => 'created_at',
                'title' => __('Created At'),
                'width' => null,
                'sortable' => true,
                'sortBy' => 'created_at',
            ],
            [
                'id' => 'actions',
                'title' => __('Actions'),
                'width' => null,
                'sortable' => false,
                'is_action' => true,
            ],
        ];
    }

    protected function buildQuery(): QueryBuilder
    {
        $query = QueryBuilder::for($this->model)
            ->with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->role, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->role);
                });
            })
            ->when($this->status, function ($query) {
                if ($this->status === 'active') {
                    $query->whereNull('banned_at');
                } elseif ($this->status === 'banned') {
                    $query->whereNotNull('banned_at');
                }
            });

        return $this->sortQuery($query);
    }

    public function renderNameColumn(User $user): Renderable
    {
        return view('backend.pages.users.partials.user-name', compact('user'));
    }

    public function renderRolesColumn(User $user): Renderable
    {
        return view('backend.pages.users.partials.user-roles', compact('user'));
    }

    public function bulkDelete(): void
    {
        $ids = $this->selectedItems;
        $ids = array_filter($ids, 'is_numeric');

        if (empty($ids)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Bulk Delete Failed'),
                'message' => __('No users selected for deletion.'),
            ]);
            return;
        }

        \Illuminate\Support\Facades\Log::info('Bulk Deleting IDs:', $ids);

        // Filter out Superadmin and Current User from the processing list
        $validUsers = User::whereIn('id', $ids)
            ->where('id', '!=', Auth::id())
            ->get()
            ->filter(function ($user) {
                return !$user->hasRole(Role::SUPERADMIN) &&
                    !$user->roles->contains(fn($r) => strcasecmp($r->name, 'superadmin') === 0);
            });

        $validIds = $validUsers->pluck('id')->toArray();

        if (empty($validIds)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Bulk Delete Failed'),
                'message' => __('Selected users are protected (Superadmin or You) and cannot be deleted.'),
            ]);
            return;
        }

        // Update selected items to only valid ones so parent logic uses clean list
        $this->selectedItems = array_map('strval', $validIds);

        // Check for dependencies within the VALID list
        $usersWithDependencies = User::whereIn('id', $validIds)
            ->withCount(['products', 'orders'])
            ->having(DB::raw('products_count + orders_count'), '>', 0)
            ->get();

        if ($usersWithDependencies->isNotEmpty()) {
            $this->usersWithDependencies = $usersWithDependencies->toArray();
            $this->usersToDeleteIds = $validIds;
            $this->confirmingForceDelete = true;
            return;
        }

        // Proceed with normal delete if no dependencies
        parent::bulkDelete();
    }

    public function cancelForceDelete()
    {
        $this->confirmingForceDelete = false;
        $this->usersWithDependencies = [];
        $this->usersToDeleteIds = [];
    }

    public function confirmedForceDelete()
    {
        $deletedCount = app(\App\Services\UserService::class)->forceBulkDeleteUsers($this->usersToDeleteIds, Auth::id());

        if ($deletedCount > 0) {
            $this->dispatch('notify', [
                'variant' => 'success',
                'title' => __('Bulk Delete Successful'),
                'message' => __(':count users deleted successfully', ['count' => $deletedCount]),
            ]);
        } else {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Bulk Delete Failed'),
                'message' => __('No users were deleted.'),
            ]);
        }

        $this->selectedItems = [];
        $this->dispatch('resetSelectedItems');
        $this->resetPage();
        $this->cancelForceDelete();
    }

    public function render(): Renderable
    {
        $this->headers = $this->getHeaders();

        return view('backend.livewire.datatable.user-datatable', [
            'headers' => $this->headers,
            'data' => $this->getData(),
            'perPage' => $this->perPage,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }

    public function handleRowDelete(Model|User $user): bool
    {
        // Prevent Superadmin deletion.
        // @phpstan-ignore-next-line
        if ($user->hasRole(Role::SUPERADMIN)) {
            throw new \Exception(__('You cannot delete a :role account.', ['role' => Role::SUPERADMIN]));
        }

        // Prevent own account deletion.
        if (Auth::id() === $user->id) {
            throw new \Exception(__('You cannot delete your own account.'));
        }

        $user = $this->addHooks(
            $user,
            UserActionHook::USER_DELETED_BEFORE,
            UserFilterHook::USER_DELETED_BEFORE
        );

        $this->authorize('delete', $user);

        $deleted = $user->delete();

        $this->addHooks(
            $user,
            UserActionHook::USER_DELETED_AFTER,
            UserFilterHook::USER_DELETED_AFTER
        );

        return $deleted;
    }

    public function getActionCellPermissions($item): array
    {
        return [
            ...parent::getActionCellPermissions($item),
            'user.login_as' => Auth::user()->canBeModified($item, $this->getPermissions()['login_as'] ?? ''),
            'user.ban' => Auth::user()->canBeModified($item, 'update'), // Assuming update permission covers ban
        ];
    }

    public function renderAfterActionEdit($user): string|Renderable
    {
        $buttons = '';

        if (Auth::user()->can('user.login_as') && $user->id !== Auth::id()) {
            $buttons .= view('backend.pages.users.partials.action-button-login-as', compact('user'))->render();
        }

        if (Auth::user()->can('update', $user) && $user->id !== Auth::id() && !$user->hasRole(Role::SUPERADMIN)) {
            $buttons .= view('backend.pages.users.partials.action-button-ban', compact('user'))->render();
        }

        return $buttons;
    }

    public function ban(User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === Auth::id()) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Action Failed'),
                'message' => __('You cannot ban your own account.'),
            ]);
            return;
        }

        if ($user->hasRole(Role::SUPERADMIN)) {
            $this->dispatch('notify', [
                'variant' => 'error',
                'title' => __('Action Failed'),
                'message' => __('You cannot ban a Superadmin.'),
            ]);
            return;
        }

        $user->update(['banned_at' => now()]);

        $this->dispatch('notify', [
            'variant' => 'success',
            'title' => __('Success'),
            'message' => __('User banned successfully.'),
        ]);
    }

    public function unban(User $user)
    {
        $this->authorize('update', $user);

        $user->update(['banned_at' => null]);

        $this->dispatch('notify', [
            'variant' => 'success',
            'title' => __('Success'),
            'message' => __('User unbanned successfully.'),
        ]);
    }
}
