<?php
namespace Middleware;

use Interface\MiddlewareInterface;
use Closure;

/**
 * Fejléc köztes réteg
 */
class Header implements MiddlewareInterface
{
    /**
     * @param mixed   $request Kérés.
     * @param Closure $next    Következö middleware.
     * @return mixed
     */
    public function handle(mixed $request, Closure $next): mixed
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Valami: akarmi');
        return $next($request);
    }
}
