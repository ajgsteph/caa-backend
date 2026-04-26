<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'certificates.create',
            'certificates.view-own',
            'certificates.view-any',
            'certificates.revoke',
            'users.manage',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $superAdmin = Role::findOrCreate(UserRole::SUPER_ADMIN->value, 'web');
        $admin = Role::findOrCreate(UserRole::ADMIN->value, 'web');
        $artist = Role::findOrCreate(UserRole::ARTIST->value, 'web');
        $gallery = Role::findOrCreate(UserRole::GALLERY->value, 'web');

        $superAdmin->syncPermissions(Permission::all());
        $admin->syncPermissions([
            'certificates.view-any',
            'certificates.revoke',
            'users.manage',
        ]);
        $artist->syncPermissions([
            'certificates.create',
            'certificates.view-own',
        ]);
        $gallery->syncPermissions([
            'certificates.view-own',
        ]);
    }
}
