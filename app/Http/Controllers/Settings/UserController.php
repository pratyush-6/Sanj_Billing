<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\CompanyContextService;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private UserManagementService $userManagementService,
        private CompanyContextService $companyContext,
    ) {}

    public function index()
    {
        $users = User::with(['roles', 'companies'])->orderBy('name')->get();

        return view('settings.users.index', ['users' => $users]);
    }

    public function create()
    {
        return view('settings.users.create', [
            'roles' => Role::pluck('name'),
            'companies' => $this->companyContext->accessibleCompanies(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->userManagementService->create($request->validated());

        return redirect()->route('settings.users.index')->with('status', 'User created.');
    }

    public function edit(User $user)
    {
        return view('settings.users.edit', [
            'user' => $user->load(['roles', 'companies']),
            'roles' => Role::pluck('name'),
            'companies' => $this->companyContext->accessibleCompanies(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->userManagementService->update($user, $request->validated());

        return redirect()->route('settings.users.index')->with('status', 'User updated.');
    }
}
