<?php

namespace App\Actions\Artwork;

use App\Models\Artwork;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveArtworkAction
{
    public function execute(User $artist, array $data, ?UploadedFile $image): Artwork
    {
        $imagePath = null;

        if ($image) {
            $imagePath = $image->store('artworks', 'public');
        }

        return Artwork::create([
            'artist_id' => $artist->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'technique' => $data['technique'] ?? null,
            'dimensions' => $data['dimensions'] ?? null,
            'year' => $data['year'] ?? null,
            'description' => $data['description'] ?? null,
            'image_path' => $imagePath,
            'signature' => $data['signature'] ?? null,
        ]);
    }
}
