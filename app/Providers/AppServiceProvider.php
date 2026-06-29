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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
                $timezone = \App\Models\SystemSetting::get('timezone', config('app.timezone'));
                date_default_timezone_set($timezone);
                config(['app.timezone' => $timezone]);
            }
        } catch (\Exception $e) {
            // Ignore during setup/migrations
        }
    }
}
