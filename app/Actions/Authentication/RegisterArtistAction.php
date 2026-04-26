<?php

namespace App\Actions\Authentication;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\ArtistProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterArtistAction
{
    /**
     * @return array{user: User, token: string}
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::create([
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'status' => AccountStatus::ACTIVE,
                'registered_at' => now(),
            ]);

            $user->assignRole(UserRole::ARTIST->value);

            ArtistProfile::create([
                'user_id' => $user->id,
                'artist_name' => $data['artist_name'],
            ]);

            $token = $user->createToken('default')->plainTextToken;

            return [
                'user' => $user->fresh(['artistProfile', 'roles']),
                'token' => $token,
            ];
        });
    }
}
