<?php

namespace Tests\Feature;

use App\Enums\CertificateStatus;
use App\Enums\PaymentStatus;
use App\Jobs\GenerateCertificateJob;
use App\Models\Certificate;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Kkiapay\Kkiapay;
use Mockery;
use Tests\TestCase;

class KkiapayWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test';

    private const URI = '/api/v1/webhooks/kkiapay';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.kkiapay.webhook_secret' => self::SECRET]);
    }

    /** Forge une requête webhook signée comme le ferait KKiaPay. */
    private function postSigned(array $payload, ?string $signature = null)
    {
        $body = json_encode($payload);
        $signature ??= hash_hmac('sha256', $body, self::SECRET);

        return $this->call('POST', self::URI, [], [], [], [
            'HTTP_X_KKIAPAY_SECRET' => $signature,
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $body);
    }

    private function mockVerify(string $status, float $amount): void
    {
        $this->mock(Kkiapay::class, function (Mockery\MockInterface $mock) use ($status, $amount): void {
            $mock->shouldReceive('verifyTransaction')
                ->andReturn((object) ['status' => $status, 'amount' => $amount]);
        });
    }

    private function pendingPayment(): Payment
    {
        $certificate = Certificate::factory()->create(['status' => CertificateStatus::PENDING]);

        return Payment::factory()->create([
            'certificate_id' => $certificate->id,
            'amount' => 10000,
            'status' => PaymentStatus::PENDING,
        ]);
    }

    public function test_successful_event_confirms_payment_and_dispatches_generation(): void
    {
        Bus::fake();
        $this->mockVerify('SUCCESS', 10000);
        $payment = $this->pendingPayment();

        $response = $this->postSigned([
            'transactionId' => 'TX123',
            'isPaymentSucces' => true,
            'amount' => 10000,
            'event' => 'transaction.success',
            'stateData' => ['payment_id' => $payment->id],
        ]);

        $response->assertOk();
        $payment->refresh();
        $this->assertSame(PaymentStatus::SUCCESSFUL, $payment->status);
        $this->assertSame('TX123', $payment->transaction_reference);
        $this->assertNotNull($payment->paid_at);
        Bus::assertDispatched(GenerateCertificateJob::class, fn ($job) => $job->certificateId === $payment->certificate_id);
    }

    public function test_failed_event_marks_payment_failed_and_keeps_certificate_pending(): void
    {
        Bus::fake();
        $this->mockVerify('FAILED', 10000);
        $payment = $this->pendingPayment();

        $this->postSigned([
            'transactionId' => 'TX124',
            'isPaymentSucces' => false,
            'amount' => 10000,
            'event' => 'transaction.failed',
            'stateData' => ['payment_id' => $payment->id],
        ])->assertOk();

        $payment->refresh();
        $this->assertSame(PaymentStatus::FAILED, $payment->status);
        $this->assertSame(CertificateStatus::PENDING, $payment->certificate->status);
        Bus::assertNotDispatched(GenerateCertificateJob::class);
    }

    public function test_amount_mismatch_is_rejected(): void
    {
        Bus::fake();
        $this->mockVerify('SUCCESS', 5000); // KKiaPay says 5000, on attend 10000
        $payment = $this->pendingPayment();

        $this->postSigned([
            'transactionId' => 'TX125',
            'isPaymentSucces' => true,
            'amount' => 5000,
            'stateData' => ['payment_id' => $payment->id],
        ])->assertOk();

        $this->assertSame(PaymentStatus::FAILED, $payment->refresh()->status);
        Bus::assertNotDispatched(GenerateCertificateJob::class);
    }

    public function test_invalid_signature_is_rejected_without_touching_payment(): void
    {
        Bus::fake();
        $payment = $this->pendingPayment();

        $this->postSigned([
            'transactionId' => 'TX126',
            'stateData' => ['payment_id' => $payment->id],
        ], signature: 'wrong-signature')->assertStatus(401);

        $this->assertSame(PaymentStatus::PENDING, $payment->refresh()->status);
        Bus::assertNotDispatched(GenerateCertificateJob::class);
    }

    public function test_already_successful_payment_is_idempotent(): void
    {
        Bus::fake();
        $this->mockVerify('SUCCESS', 10000);
        $certificate = Certificate::factory()->create(['status' => CertificateStatus::ACTIVE]);
        $payment = Payment::factory()->successful()->create([
            'certificate_id' => $certificate->id,
            'amount' => 10000,
            'transaction_reference' => 'TX-OLD',
        ]);

        $this->postSigned([
            'transactionId' => 'TX-OLD',
            'isPaymentSucces' => true,
            'amount' => 10000,
            'stateData' => ['payment_id' => $payment->id],
        ])->assertOk();

        Bus::assertNotDispatched(GenerateCertificateJob::class);
        $this->assertSame('TX-OLD', $payment->refresh()->transaction_reference);
    }
}
