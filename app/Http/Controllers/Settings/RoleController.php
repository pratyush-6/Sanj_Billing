<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get();

        return view('settings.roles.index', ['roles' => $roles]);
    }

    public function create()
    {
        return view('settings.roles.create');
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);

        return redirect()->route('settings.roles.edit', $role)->with('status', "Role \"{$role->name}\" created. Set its permissions below.");
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();

        $grouped = $permissions->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0]);

        $assigned = $role->permissions->pluck('name')->all();

        return view('settings.roles.edit', [
            'role' => $role,
            'grouped' => $grouped,
            'assigned' => $assigned,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        // Super Admin always has every permission — never editable, so an
        // admin can't accidentally lock everyone (including themselves) out.
        if ($role->name === 'Super Admin') {
            return redirect()->route('settings.roles.edit', $role)->with('error', 'The Super Admin role always has every permission and cannot be edited.');
        }

        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('settings.roles.index')->with('status', "Permissions updated for {$role->name}.");
    }
}
