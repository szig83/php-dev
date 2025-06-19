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
        #fileWrite(__CLASS__);
        if ($request['protected']) {
            if (!headers_sent()) {
                header('Location: /', true, 401);
                exit();
            } else {
                echo 'Headers already sent';
            }
        }
        var_dump($request);
        return $next($request);
    }
}
