<?php

namespace App\Providers;

use App\Models\CourseCertificate;
use App\Policies\CourseCertificatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        CourseCertificate::class => CourseCertificatePolicy::class,
    ];
}
