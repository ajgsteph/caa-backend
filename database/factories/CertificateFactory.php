<?php

namespace Database\Factories;

use App\Enums\CertificateStatus;
use App\Models\Artwork;
use App\Models\Certificate;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        $year = (int) date('Y');
        $sequence = fake()->unique()->numberBetween(1, 9999);
        $number = sprintf('CAA-%d-%04d', $year, $sequence);

        return [
            'unique_number' => $number,
            'artwork_id' => Artwork::factory(),
            'client_id' => Client::factory(),
            'artist_id' => User::factory(),
            'certified_at' => now(),
            'verification_url' => rtrim(config('app.url'), '/').'/api/v1/verify/'.$number,
            'qr_code_path' => null,
            'pdf_path' => null,
            'status' => CertificateStatus::ACTIVE,
        ];
    }
}
