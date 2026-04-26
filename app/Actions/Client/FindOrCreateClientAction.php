<?php

namespace App\Actions\Client;

use App\Models\Client;

class FindOrCreateClientAction
{
    public function execute(array $data): Client
    {
        return Client::firstOrCreate(
            ['email' => $data['email']],
            [
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'phone' => $data['phone'] ?? null,
            ]
        );
    }
}
