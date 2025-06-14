<?php

namespace App\Core;

/**
 * Debugger osztály
 */
class Debugger
{

    /**
     * @var Application
     */
    public Application $app;

    /**
     * @param Application $app Alkalmazás objektum.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return void
     */
    public function render(): void
    {
        var_dump($_SESSION);
        var_dump($this->app->config->getAll());
    }
}
