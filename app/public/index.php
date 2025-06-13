<?php
require_once __DIR__ . '/../vendor/autoload.php';

$app = new App\Core\Application(Enum\Context::PUBLIC);

echo $app->render();

/*
$app->logger->error('Ajjaj');
$app->logger->warning('warning');
$app->logger->info('info');
$app->logger->notice('notice');
$app->logger->debug('debug');

*/




/*
echo '<pre>';
print_r($app->config->getAll());
echo '</pre>';
*/