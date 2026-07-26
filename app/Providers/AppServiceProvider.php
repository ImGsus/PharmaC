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
        if ($this->app->runningInConsole()) {
            URL::forceRootUrl(config('app.url'));
        } else {
            try {
                $request = request();
                URL::forceRootUrl($request->getSchemeAndHttpHost());
                URL::forceScheme($request->getScheme());
            } catch (\Throwable $e) {
                // no request available, use app url
                URL::forceRootUrl(config('app.url'));
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
