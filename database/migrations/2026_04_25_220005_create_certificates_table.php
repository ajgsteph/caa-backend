<?php

use App\Enums\CertificateStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('unique_number')->unique();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artist_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('certified_at')->nullable();
            $table->string('verification_url');
            $table->string('qr_code_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('status')->default(CertificateStatus::PENDING->value);
            $table->text('revocation_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
