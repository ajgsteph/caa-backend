<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kkiapay\Kkiapay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Client KKiaPay partagé (injectable + mockable dans les tests).
        $this->app->singleton(Kkiapay::class, function (): Kkiapay {
            $config = config('services.kkiapay');

            return new Kkiapay(
                $config['public_key'],
                $config['private_key'],
                $config['secret_key'],
                (bool) $config['sandbox'],
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
