<?php

namespace App\Providers;

use App\Http\Services\V1\RoleService;
use App\Http\Services\V1\CategoryService;
use App\Http\Services\V1\LogService;
use App\Http\Services\V1\NotificationService;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RoleService::class);
        $this->app->singleton(CategoryService::class);
        $this->app->singleton(LogService::class);
        $this->app->singleton(NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
