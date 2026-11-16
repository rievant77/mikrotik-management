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
        \Carbon\Carbon::setLocale('id');
        \Illuminate\Support\Carbon::setLocale('id');

        \Illuminate\Support\Facades\Blade::directive('formatDate', function ($expression) {
            return "<?php echo \App\Support\FormatHelper::formatDate($expression); ?>";
        });
        \Illuminate\Support\Facades\Blade::directive('formatDateTime', function ($expression) {
            return "<?php echo \App\Support\FormatHelper::formatDateTime($expression); ?>";
        });
        \Illuminate\Support\Facades\Blade::directive('formatDateShort', function ($expression) {
            return "<?php echo \App\Support\FormatHelper::formatDateShort($expression); ?>";
        });
        \Illuminate\Support\Facades\Blade::directive('formatDateTimeShort', function ($expression) {
            return "<?php echo \App\Support\FormatHelper::formatDateTimeShort($expression); ?>";
        });
        \Illuminate\Support\Facades\Blade::directive('formatNumber', function ($expression) {
            return "<?php echo \App\Support\FormatHelper::formatNumber($expression); ?>";
        });
        \Illuminate\Support\Facades\Blade::directive('formatRupiah', function ($expression) {
            return "<?php echo \App\Support\FormatHelper::formatRupiah($expression); ?>";
        });

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
