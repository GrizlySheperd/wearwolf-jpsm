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
     * insecure. This reads the raw X-Forwarded-Proto header directly rather
     * than trusting Request::isSecure() -- FrankenPHP/Caddy (which now
     * terminates the connection from Railway's edge itself) does not
     * reliably surface that header through Laravel's own trustProxies()
     * resolution, so $request->isSecure() kept reporting secure requests as
     * insecure (or vice versa) through that chain. Railway is the only
     * possible ingress to this container, so any X-Forwarded-Proto value
     * that reaches us here can be trusted outright. Skip the health check
     * route since Railway's prober hits it directly without TLS and won't
     * follow a redirect.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $proto = $request->header('X-Forwarded-Proto');
        $isSecure = $proto ? strtolower($proto) === 'https' : $request->isSecure();

        if (! $isSecure && app()->environment('production') && ! $request->is('up')) {
            return redirect('https://'.$request->getHttpHost().$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
