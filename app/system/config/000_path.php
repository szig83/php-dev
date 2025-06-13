<?php

$documentRoot = $_SERVER["DOCUMENT_ROOT"] ?? null;

if (!empty($documentRoot)) {
    $appRoot = substr(
        $documentRoot,
        0,
        strripos($documentRoot, DIRECTORY_SEPARATOR)
    );
}

if (empty($appRoot)) {
    return [];
}

return [
    'server' => [
        'app' => $appRoot,
        'cache' => $appRoot . DIRECTORY_SEPARATOR . 'system_cache',
        'document' => $documentRoot,
        'log' => $appRoot . DIRECTORY_SEPARATOR . 'system_log' . DIRECTORY_SEPARATOR . 'app',
        'temp' => $appRoot . DIRECTORY_SEPARATOR . 'system_temp',
        'upload' => $appRoot . DIRECTORY_SEPARATOR . 'system_upload'
    ]
];
