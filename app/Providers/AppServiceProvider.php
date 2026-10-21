<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Railway is the only possible ingress to this container, and
        // FrankenPHP/Caddy's own scheme detection (which trustProxies()
        // ultimately depends on) has proven unreliable through that chain --
        // forms kept rendering with an http:// action even after trusting
        // proxies. Since this app is only ever served at one known https
        // URL, just force every generated URL to https unconditionally
        // rather than relying on per-request scheme detection at all.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
