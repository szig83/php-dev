<?php

namespace App\Core;

class Theme {
    public array $headElements = [];
    public string $body = '';

    private Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function addHeadElement($element) {
        $this->headElements[] = $element;
    }
}
