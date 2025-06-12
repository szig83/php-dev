<?php
return [
    // Alapértelmezett beállítások (felület nélküli)
    'name' => 'MyApp',
    'debug' => false,
    'timezone' => 'Europe/Budapest',
    // Felület-specifikus beállítások
    'public' => [
        'name' => 'MyApp Public',
        'debug' => true,
        'theme' => 'default',
    ],
    'admin' => [
        'name' => 'MyApp Admin',
        'debug' => false,
        'theme' => 'admin-dark',
    ],
];
