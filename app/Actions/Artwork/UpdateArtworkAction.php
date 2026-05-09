<?php

namespace App\Actions\Artwork;

use App\Models\Artwork;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateArtworkAction
{
    public function execute(Artwork $artwork, array $data, ?UploadedFile $image): Artwork
    {
        if ($image) {
            if ($artwork->image_path) {
                Storage::disk('public')->delete($artwork->image_path);
            }
            $data['image_path'] = $image->store('artworks', 'public');
        }

        $fields = ['title', 'type', 'technique', 'dimensions', 'year', 'description', 'signature', 'image_path'];
        $artwork->update(array_intersect_key($data, array_flip($fields)));

        return $artwork->fresh();
    }
}
