<?php

namespace App\Core;

use Monolog\Logger;
use Enum\Context;
use Middleware\Header;

/**
 * Az alkalmazás alap osztálya
 */
class Application
{
    /**
     * @var Config Az alkalmazás konfigurációja.
     */
    public Config $config;

    /**
     * @var Logger Az alkalmazás naplózásos osztálya.
     */
    public Logger $logger;

    /**
     * @var Router Az alkalmazás router osztálya.
     */
    public Router $router;

    /**
     * @var Middleware A middleware pipeline.
     */
    private Middleware $pipeline;

    /**
     * Osztály konstruktor
     * @param Context|null $context Az alkalmazás kontextusa (public, admin, stb.).
     */
    public function __construct(?Context $context = null)
    {
        $this->pipeline = new Middleware();
        $this->pipeline->add(new Header());
        $this->setErrorHandler();
        $this->config = Config::getInstance($context->value);
        $this->logger = Log::getInstance($this->config)->logger;
        $this->router = new Router($this->config);
    }

    /**
     * Hibakezelés hozzáadása
     * @return void
     */
    private function setErrorHandler():void
    {
        register_shutdown_function(function () {
            $last_error = error_get_last();
            if (!empty($last_error)) {
                $this->logger->error($last_error['message']);
                if ($last_error['type'] === E_ERROR) {
                    echo 'Fatal error';
                }
            }
        });

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $dbt = debug_backtrace();
            $errline = !empty($dbt[2]['line']) ? $dbt[2]['line'] : $errline;
            $errfile = !empty($dbt[2]['file']) ? $dbt[2]['file'] : $errfile;
            $this->logger->error($errstr, [$errfile. ' ('.$errline.')']);
        });
    }

    /**
     * Middleware-ek futtatása a kérésen
     * @param mixed    $request A kérés
     * @param callable $core    A core kéréskezelő függvény
     * @return mixed
     */
    public function runMiddlewares(mixed $request, callable $core)
    {
        return $this->pipeline->handle($request, $core);
    }

    /**
     * Oldal renderelése middleware-ekkel
     * @param mixed $request A kérés adatai (opcionális).
     * @return string
     */
    public function render(mixed $request = []): string
    {
        $coreRenderer = function ($req) {
            ob_start();
            $this->router->loadRoute();
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        };
        
        return $this->runMiddlewares($request, $coreRenderer);
    }
}
