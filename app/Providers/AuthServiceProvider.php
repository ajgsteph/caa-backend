<?php

namespace App\Providers;

use App\Models\Certificate;
use App\Policies\CertificatePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Certificate::class, CertificatePolicy::class);
    }
}
