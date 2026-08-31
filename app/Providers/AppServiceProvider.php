<?php

namespace App\Providers;

use App\Models\RouterSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        View::composer('*', function ($view) {
            static $setting = null;
            if ($setting === null) {
                try {
                    if (Schema::hasTable('router_settings')) {
                        $setting = RouterSetting::first();
                    }
                } catch (\Exception $e) {
                    $setting = null;
                }
            }
            $view->with('appSetting', $setting);
        });
    }
}
