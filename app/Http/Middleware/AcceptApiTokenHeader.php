<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Some shared hosts (Apache/LiteSpeed behind a rewrite) strip the Authorization header,
 * which makes every Sanctum-protected route answer "Unauthenticated". The dashboard also
 * sends its API token in X-Api-Token; restore it as a Bearer token when the original is missing.
 */
class AcceptApiTokenHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = trim((string) $request->header('X-Api-Token'));

        if ($token !== '' && ! $request->bearerToken()) {
            $request->headers->set('Authorization', 'Bearer '.$token);
        }

        return $next($request);
    }
}
