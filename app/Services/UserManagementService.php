<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    public function __construct(private AuditLogService $auditLog) {}

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'active',
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);

            $user->syncRoles([$data['role']]);
            $user->companies()->sync($data['companies'] ?? []);

            $this->auditLog->log('User Created', 'User', $user, null, $user->toArray());

            return $user;
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $old = $user->toArray();

            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? $user->status,
            ]);

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            if (array_key_exists('companies', $data)) {
                $user->companies()->sync($data['companies'] ?? []);
            }

            $this->auditLog->log('User Updated', 'User', $user, $old, $user->toArray());

            return $user;
        });
    }
}
