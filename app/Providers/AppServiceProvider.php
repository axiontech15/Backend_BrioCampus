<?php

namespace App\Providers;

use App\Http\Services\V1\RoleService;
use App\Http\Services\V1\CategoryService;
use App\Http\Services\V1\LogService;
use App\Http\Services\V1\NotificationService;
use App\Http\Services\V1\TicketService;
use App\Http\Services\V1\UniversityService;
use App\Http\Services\V1\UserService;
use App\Http\Services\V1\PalmaresService;
use App\Http\Services\V1\PaymentService;
use App\Http\Services\V1\ProfileSearchService;
// use App\Http\Services\V1\

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
        $this->app->singleton(TicketService::class);
        $this->app->singleton(UniversityService::class);
        $this->app->singleton(UserService::class);
        $this->app->singleton(PalmaresService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(ProfileSearchService::class);
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
