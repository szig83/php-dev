<?php
namespace Middleware;

use Interface\MiddlewareInterface;
use Closure;

/**
 * Fejléc köztes réteg
 */
class Auth implements MiddlewareInterface
{
    /**
     * @param mixed   $request Kérés.
     * @param Closure $next    Következö middleware.
     * @return mixed
     */
    public function handle(mixed $request, Closure $next): mixed
    {
        fileWrite(__CLASS__);
        if (!headers_sent()) {
            header('Location: /');
            exit();
        }else {
            echo 'Headers already sent';
        }
        return $next($request);
    }
}
