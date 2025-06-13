<?php

namespace App\Core;

/**
 * Router osztály
 */
class Router
{

    /**
     * @var array|string[]
     */
    private array $urlParams;

    /**
     * @var array
     */
    private array $queryParams = [];

    /**
     * @var array
     */
    private array $postDatas = [];

    /**
     * @var Config
     */
    private Config $config;

    /**
     * Router osztály konstruktor
     * @param Config $config Konfiguráció.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $uri = $_SERVER['REQUEST_URI'];
        $uriParts = explode('?', $uri);
        $path = $uriParts[0];
        $uri = explode('/', $path);
        array_shift($uri); // Eltávolítjuk az első üres elemet
        $this->urlParams = $uri;

        // Query paraméterek feldolgozása, ha vannak
        if (isset($uriParts[1])) {
            parse_str($uriParts[1], $this->queryParams);
        }

        // POST adatok feldolgozása, ha vannak
        if (isset($_POST)) {
            $this->postDatas = $_POST;
        }

        $this->sanitizeParameters();
    }

    /**
     * Aktuális oldal SEO azonosítójának lekérése
     * @return string
     */
    public function getActualPage(): string
    {
        return $this->urlParams[0];
    }

    /**
     * Biztonsági okokból megtisztítja a bemeneti paramétereket, hogy megelőzzük a hackelést és a rosszindulatú kódot.
     * @param mixed $input A megtisztítandó bemenet.
     * @return mixed A megtisztított bemenet.
     */
    private function sanitizeInput(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        } elseif (is_string($input)) {
            // HTML tagok eltávolítása és speciális karakterek kódolása
            $input = strip_tags($input);
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            // SQL injection elleni védekezés
            return addslashes($input);
        }
        return $input;
    }

    /**
     * Minden paramétert megtisztít (URL, lekérdezési, és POST adatok).
     * @return void
     */
    private function sanitizeParameters(): void
    {
        $this->urlParams = array_map([$this, 'sanitizeInput'], $this->urlParams);
        $this->queryParams = array_map([$this, 'sanitizeInput'], $this->queryParams);
        $this->postDatas = array_map([$this, 'sanitizeInput'], $this->postDatas);
    }

    /**
     * Oldal betöltése
     * @return void
     */
    public function loadRoute(): void
    {
        $routePath = $this->config->get('path.server.app') . '/context/public/view/'.$this->getActualPage().'.php';
        if (file_exists($routePath)) {
            require_once $routePath;
        } else {
            echo '404';
        }
    }
}
