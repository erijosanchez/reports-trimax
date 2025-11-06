<?php

namespace App\Providers;

use App\Models\Dashboard;
use App\Models\UploadedFile;
use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Policies\FilePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Dashboard::class => DashboardPolicy::class,
        UploadedFile::class => FilePolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}