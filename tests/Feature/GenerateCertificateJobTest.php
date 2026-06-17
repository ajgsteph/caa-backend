<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Jobs\GenerateCertificateJob;
use App\Jobs\GeneratePdfJob;
use App\Jobs\GenerateQrCodeJob;
use App\Jobs\SendCertificateEmailJob;
use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class GenerateCertificateJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_activates_certificate_and_chains_generation(): void
    {
        Bus::fake();
        $certificate = Certificate::factory()->create([
            'status' => CertificateStatus::PENDING,
            'certified_at' => null,
        ]);

        (new GenerateCertificateJob($certificate->id))->handle();

        $certificate->refresh();
        $this->assertSame(CertificateStatus::ACTIVE, $certificate->status);
        $this->assertNotNull($certificate->certified_at);

        Bus::assertChained([
            GenerateQrCodeJob::class,
            GeneratePdfJob::class,
            SendCertificateEmailJob::class,
        ]);
    }

    public function test_it_is_a_noop_for_a_missing_certificate(): void
    {
        Bus::fake();

        (new GenerateCertificateJob(999999))->handle();

        Bus::assertNothingDispatched();
    }
}
