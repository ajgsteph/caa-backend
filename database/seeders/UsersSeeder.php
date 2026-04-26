<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\ArtistProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::create([
            'last_name' => 'Root',
            'first_name' => 'Super',
            'email' => 'superadmin@caa.sn',
            'password' => 'superpass',
            'phone' => '+221770000001',
            'status' => AccountStatus::ACTIVE,
            'registered_at' => now(),
        ]);
        $superAdmin->assignRole(UserRole::SUPER_ADMIN->value);

        $admin = User::create([
            'last_name' => 'Sow',
            'first_name' => 'Aminata',
            'email' => 'admin@caa.sn',
            'password' => 'adminpass',
            'phone' => '+221770000002',
            'status' => AccountStatus::ACTIVE,
            'registered_at' => now(),
        ]);
        $admin->assignRole(UserRole::ADMIN->value);

        $artist1 = User::create([
            'last_name' => 'Diop',
            'first_name' => 'Awa',
            'email' => 'awa@caa.sn',
            'password' => 'awapass',
            'phone' => '+221770000003',
            'status' => AccountStatus::ACTIVE,
            'registered_at' => now(),
        ]);
        $artist1->assignRole(UserRole::ARTIST->value);
        ArtistProfile::create(['user_id' => $artist1->id, 'artist_name' => 'AWA-D']);

        $artist2 = User::create([
            'last_name' => 'Sarr',
            'first_name' => 'Modou',
            'email' => 'modou@caa.sn',
            'password' => 'modoupass',
            'phone' => '+221770000004',
            'status' => AccountStatus::ACTIVE,
            'registered_at' => now(),
        ]);
        $artist2->assignRole(UserRole::ARTIST->value);
        ArtistProfile::create(['user_id' => $artist2->id, 'artist_name' => 'M-SARR']);
    }
}
