<?php
namespace Middleware;

use Interface\MiddlewareInterface;
use Closure;

/**
 * Fejléc köztes réteg
 */
class Stat implements MiddlewareInterface
{
    /**
     * @param mixed   $request Kérés.
     * @param Closure $next    Következö middleware.
     * @return mixed
     */
    public function handle(mixed $request, Closure $next): mixed
    {
        fileWrite(__CLASS__);
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Mentsük el az executionTime-ot a Request objektumhoz
        $_SESSION['executionTime'] = $executionTime;
        return $response;
    }


}
