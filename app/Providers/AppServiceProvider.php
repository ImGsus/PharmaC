<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register application services here if needed.
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $appUrl = (string) config('app.url');
        $isHttps = $this->app->environment('production')
            || str_starts_with($appUrl, 'https://')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (request()->server('HTTP_X_FORWARDED_PROTO') === 'https')
            || request()->isSecure();

        if ($isHttps) {
            URL::forceScheme('https');
        }

        if ($this->app->runningInConsole() && !empty($appUrl)) {
            URL::forceRootUrl($appUrl);
            if ($isHttps) {
                URL::forceScheme('https');
            }
        }

        $this->app->extend('command.migrate', function ($command, $app) {
            return new \App\Console\MigrateCommand(
                $app['migrator'],
                $app[Dispatcher::class]
            );
        });
    }
}
