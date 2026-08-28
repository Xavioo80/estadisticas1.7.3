<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\RegistroGlobalRepositoryInterface::class ,
            \App\Repositories\RegistroGlobalRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\RegistroGlobal::observe(\App\Observers\RegistroGlobalObserver::class);

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            static $cachedLogoUrl = null;
            if ($cachedLogoUrl === null) {
                $cachedLogoUrl = \Illuminate\Support\Facades\Cache::remember('app_general_logo_url', 3600, function() {
                    $logoUrl = asset('img/LOGO CIS.jpeg');
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('login_settings')) {
                            $loginSettings = \App\Models\LoginSetting::first();
                            if ($loginSettings && $loginSettings->logo_path) {
                                $logoUrl = asset('storage/' . $loginSettings->logo_path);
                            }
                        }
                    } catch (\Throwable $e) {
                        // Fallback gracefully
                    }
                    return $logoUrl;
                });
            }
            $view->with('generalLogoUrl', $cachedLogoUrl);
        });
    }
}
