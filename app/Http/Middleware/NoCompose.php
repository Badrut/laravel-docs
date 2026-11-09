<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCompose
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $request->headers->remove('Accept-Encoding');
        $response = $next($request);
        $response->headers->set('Content-Encoding', 'identity');
        $response->headers->set('Cache-Control', 'no-store');
        return $response;
    }
}
