<?php

namespace Database\Factories;

use App\Actions\Payment\InitiatePaymentAction;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Certificate;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'certificate_id' => Certificate::factory(),
            'amount' => InitiatePaymentAction::CERTIFICATE_PRICE_FCFA,
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'status' => PaymentStatus::PENDING,
            'paid_at' => null,
            'transaction_reference' => null,
        ];
    }

    public function successful(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::SUCCESSFUL,
            'paid_at' => now(),
            'transaction_reference' => 'TX-'.fake()->unique()->numerify('########'),
        ]);
    }
}
