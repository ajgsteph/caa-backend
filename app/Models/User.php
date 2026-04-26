<?php

namespace App\Models;

use App\Enums\AccountStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'password',
        'phone',
        'status',
        'registered_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'registered_at' => 'datetime',
            'password' => 'hashed',
            'status' => AccountStatus::class,
        ];
    }

    public function artistProfile(): HasOne
    {
        return $this->hasOne(ArtistProfile::class);
    }

    public function galleryProfile(): HasOne
    {
        return $this->hasOne(GalleryProfile::class);
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class, 'artist_id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'artist_id');
    }

    public function isActive(): bool
    {
        return $this->status?->canLogin() ?? true;
    }
}
