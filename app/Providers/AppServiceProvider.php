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
        $isHttps = str_starts_with($appUrl, 'https://')
            || $this->app->environment('production')
            || (request()->server('HTTP_X_FORWARDED_PROTO') === 'https');

        if ($isHttps) {
            URL::forceScheme('https');
        }

        if ($this->app->runningInConsole()) {
            URL::forceRootUrl($appUrl);
            if ($isHttps) {
                URL::forceScheme('https');
            }
        } else {
            try {
                $request = request();
                URL::forceRootUrl($request->getSchemeAndHttpHost());
                if ($isHttps || $request->isSecure()) {
                    URL::forceScheme('https');
                } else {
                    URL::forceScheme($request->getScheme());
                }
            } catch (\Throwable $e) {
                // no request available, use app url
                URL::forceRootUrl($appUrl);
                if ($isHttps) {
                    URL::forceScheme('https');
                }
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
