<?php

namespace App\Models;

use App\Enums\CertificateStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_number',
        'artwork_id',
        'client_id',
        'artist_id',
        'certified_at',
        'verification_url',
        'qr_code_path',
        'pdf_path',
        'status',
        'revocation_reason',
    ];

    protected function casts(): array
    {
        return [
            'certified_at' => 'datetime',
            'status' => CertificateStatus::class,
        ];
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function isValid(): bool
    {
        return $this->status === CertificateStatus::ACTIVE;
    }
}
