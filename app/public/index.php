<?php
session_start();

function fileWrite(string $message): void
{
    $logFile = fopen('x.log', 'a');
    fwrite($logFile, $message . PHP_EOL);
    fclose($logFile);
}

require_once __DIR__ . '/../vendor/autoload.php';

use Middleware\Stat;
use Middleware\Auth;

$app = new App\Core\Application(Enum\Context::PUBLIC);
$app->addMiddleware(new Stat());
$app->addMiddleware(new Auth());

echo $app->render();

if (isset($app->debugger)) {
    $app->debugger->render();
}