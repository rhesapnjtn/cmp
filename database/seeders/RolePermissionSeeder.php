<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionMap = [];
        foreach (config('permissions.permissions') as $p) {
            $permissionMap[$p['code']] = Permission::updateOrCreate(['code' => $p['code']], $p);
        }

        foreach (config('permissions.roles') as $code => $permCodes) {
            $role = Role::updateOrCreate(['code' => $code], [
                'name' => ucfirst($code),
                'description' => "Role: {$code}",
            ]);

            $role->permissions()->sync(collect($permCodes)->map(
                fn ($c) => $permissionMap[$c]->id
            ));
        }

        // Remove permissions that are no longer defined
        Permission::whereNotIn('code', array_column(config('permissions.permissions'), 'code'))->delete();
    }
}