<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Support\SuperAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    public const SCOPE_USER = 'user';

    public const SCOPE_ADMIN = 'admin';

    /** @var list<string> */
    private const PROFILE_KEYS = [
        'first_name',
        'last_name',
        'company_name',
        'owns_machines',
        'machine_types',
        'machines_needed',
        'primary_city',
    ];

    public function index(Request $request): Response
    {
        $scope = $this->scope($request);

        $search = trim((string) $request->query('search', ''));
        $roleName = $this->forcedRole($scope);

        $users = User::query()
            ->with('roles:id,name')
            ->role($roleName)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
            'scope' => $scope,
            'superAdminCount' => SuperAdmin::count(),
            'stats' => Inertia::optional(fn (): array => [
                'total' => User::role($roleName)->count(),
                'verified' => User::role($roleName)->whereNotNull('email_verified_at')->count(),
            ]),
        ]);
    }

    public function create(Request $request): Response
    {
        $scope = $this->scope($request);

        return Inertia::render('admin/users/create', [
            'scope' => $scope,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $scope = $this->scope($request);

        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($scope === self::SCOPE_USER) {
            $data['name'] = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));
        }

        $user = User::create(collect($data)->except([
            'roles',
            'remove_avatar',
            ...self::PROFILE_KEYS,
        ])->all());

        $user->syncRoles([$this->forcedRole($scope)]);

        if ($scope === self::SCOPE_USER) {
            $this->syncOperatorProfile($user, $data);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $scope === self::SCOPE_ADMIN
                ? 'Admin created successfully.'
                : 'User created successfully.',
        ]);

        return redirect()->route($this->routeName($scope, 'index'));
    }

    public function show(Request $request, User $user): Response
    {
        $scope = $this->scope($request);
        $this->ensureUserInScope($user, $scope);

        $user->load('roles:id,name', 'permissions:id,name');

        if ($scope === self::SCOPE_USER) {
            $user->load('operatorProfile');
        }

        return Inertia::render('admin/users/show', [
            'user' => $user,
            'scope' => $scope,
            'operatorProfile' => $scope === self::SCOPE_USER
                ? $user->operatorProfile
                : null,
        ]);
    }

    public function edit(Request $request, User $user): Response
    {
        $scope = $this->scope($request);
        $this->ensureUserInScope($user, $scope);
        $this->authorize('update', $user);

        $user->load('roles:id,name');

        if ($scope === self::SCOPE_USER) {
            $user->load('operatorProfile');
        }

        return Inertia::render('admin/users/edit', [
            'user' => $user,
            'scope' => $scope,
            'userRoles' => $user->roles->pluck('name'),
            'isLastSuperAdmin' => SuperAdmin::isLast($user),
            'operatorProfile' => $scope === self::SCOPE_USER
                ? $user->operatorProfile
                : null,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $scope = $this->scope($request);
        $this->ensureUserInScope($user, $scope);
        $this->authorize('update', $user);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar($user);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            $this->deleteAvatar($user);
            $data['avatar'] = null;
        }

        if ($scope === self::SCOPE_USER) {
            $data['name'] = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));
        }

        $user->update(collect($data)->except([
            'roles',
            'remove_avatar',
            ...self::PROFILE_KEYS,
        ])->all());

        $user->syncRoles([$this->forcedRole($scope)]);

        if ($scope === self::SCOPE_USER) {
            $this->syncOperatorProfile($user, $data);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $scope === self::SCOPE_ADMIN
                ? 'Admin updated successfully.'
                : 'User updated successfully.',
        ]);

        return redirect()->route($this->routeName($scope, 'index'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $scope = $this->scope($request);
        $this->ensureUserInScope($user, $scope);
        $this->authorize('delete', $user);

        if (SuperAdmin::isLast($user)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You must assign the super-admin role to another user before deleting the last super administrator.',
            ]);

            return redirect()->back();
        }

        $this->deleteAvatar($user);
        $user->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $scope === self::SCOPE_ADMIN
                ? 'Admin deleted successfully.'
                : 'User deleted successfully.',
        ]);

        return redirect()->back();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncOperatorProfile(User $user, array $data): void
    {
        $payload = [
            'first_name' => (string) $data['first_name'],
            'last_name' => (string) $data['last_name'],
            'company_name' => (string) $data['company_name'],
            'owns_machines' => (bool) $data['owns_machines'],
            'machine_types' => array_values(array_filter(
                array_map(
                    static fn (mixed $type): string => trim((string) $type),
                    is_array($data['machine_types'] ?? null) ? $data['machine_types'] : [],
                ),
                static fn (string $type): bool => $type !== '',
            )),
            'machines_needed' => (string) $data['machines_needed'],
            'primary_city' => (string) $data['primary_city'],
        ];

        $profile = $user->operatorProfile;

        if ($profile === null) {
            $user->operatorProfile()->create([
                ...$payload,
                'terms_accepted_at' => now(),
            ]);

            return;
        }

        $profile->update($payload);
    }

    private function scope(Request $request): string
    {
        if ($request->routeIs('admin.admin-management.*')) {
            return self::SCOPE_ADMIN;
        }

        return self::SCOPE_USER;
    }

    private function forcedRole(string $scope): string
    {
        return $scope === self::SCOPE_ADMIN
            ? RoleEnum::ADMIN->value
            : RoleEnum::USER->value;
    }

    private function routeName(string $scope, string $action): string
    {
        $prefix = $scope === self::SCOPE_ADMIN
            ? 'admin.admin-management'
            : 'admin.user-management';

        return "{$prefix}.{$action}";
    }

    private function ensureUserInScope(User $user, string $scope): void
    {
        if (! $user->hasRole($this->forcedRole($scope))) {
            throw new NotFoundHttpException;
        }
    }

    private function deleteAvatar(User $user): void
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
    }
}
