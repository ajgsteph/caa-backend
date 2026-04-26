<?php

namespace Database\Factories;

use App\Enums\ArtworkType;
use App\Models\Artwork;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artwork>
 */
class ArtworkFactory extends Factory
{
    protected $model = Artwork::class;

    public function definition(): array
    {
        return [
            'artist_id' => User::factory(),
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(ArtworkType::cases()),
            'technique' => fake()->word(),
            'dimensions' => fake()->numberBetween(20, 200).'x'.fake()->numberBetween(20, 200).' cm',
            'year' => fake()->numberBetween(1980, (int) date('Y')),
            'description' => fake()->paragraph(),
            'image_path' => null,
            'signature' => fake()->lastName(),
        ];
    }
}
