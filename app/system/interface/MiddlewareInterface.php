<?php

namespace Interface;

use Closure;

interface MiddlewareInterface
{
    /**
     * @param $request Kérés.
     * @param Closure $next Következö middleware.
     * @return mixed
     */
    public function handle($request, Closure $next): mixed;
}
