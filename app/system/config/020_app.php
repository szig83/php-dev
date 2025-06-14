<?php
return [
    // Alapértelmezett beállítások (felület nélküli)
    'name' => 'MyApp',
    'debug' => false,
    'timezone' => 'Europe/Budapest',
    // Felület-specifikus beállítások
    'public' => [
        'name' => 'MyApp Public',
        'theme' => 'default',
    ],
    'admin' => [
        'name' => 'MyApp Admin',
        'theme' => 'admin-dark',
    ],
];
