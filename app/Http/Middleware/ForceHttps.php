<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Redirect insecure requests to HTTPS in production.
     *
     * Railway's edge accepts plain HTTP without upgrading it, so a request
     * that never had TLS terminated in front of it reaches us as genuinely
     * insecure -- trusting X-Forwarded-Proto (see bootstrap/app.php) only
     * fixes the scheme Laravel *generates* in its own links, it does not
     * redirect the browser away from an insecure page it already loaded.
     * Skip the health check route since Railway's prober hits it directly
     * without TLS and won't follow a redirect.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isSecure() && app()->environment('production') && ! $request->is('up')) {
            return redirect('https://'.$request->getHttpHost().$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
