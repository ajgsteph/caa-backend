<?php

namespace App\Actions\Artwork;

use App\Models\Artwork;
use Illuminate\Support\Facades\Storage;

class DeleteArtworkAction
{
    public function execute(Artwork $artwork): void
    {
        if ($artwork->image_path) {
            Storage::disk('public')->delete($artwork->image_path);
        }

        $artwork->delete();
    }
}
