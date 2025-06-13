<?php

namespace App\Core;

use Interface\MiddlewareInterface;

/**
 * Middleware osztály
 */
class Middleware
{
    /**
     * @var array
     */
    private array $middleware = [];

    /**
     * Middleware hozzáadása a lánchoz
     * @param MiddlewareInterface $middleware Middleware objektum.
     * @return void
     */
    public function add(MiddlewareInterface $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Kérés futtatása a middleware láncon keresztül
     * @param mixed    $request Kérés.
     * @param callable $core    Core függvény.
     * @return mixed
     */
    public function handle(mixed $request, callable $core): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    return $middleware->handle($request, $next);
                };
            },
            function ($request) use ($core) {
                return $core($request);
            }
        );
        return $pipeline($request);
    }
}
