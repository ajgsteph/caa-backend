<?php

namespace App\Actions\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    /**
     * @return array{user: User, token: string}
     */
    public function execute(string $email, string $password, ?string $deviceName = null): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte est suspendu.'],
            ]);
        }

        $token = $user->createToken($deviceName ?? 'default')->plainTextToken;

        return [
            'user' => $user->load(['artistProfile', 'roles']),
            'token' => $token,
        ];
    }
}
