<?php

namespace App\Models;

use App\Enums\ArtworkType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Artwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'title',
        'type',
        'technique',
        'dimensions',
        'year',
        'description',
        'image_path',
        'signature',
    ];

    protected function casts(): array
    {
        return [
            'type' => ArtworkType::class,
            'year' => 'integer',
        ];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }
}
