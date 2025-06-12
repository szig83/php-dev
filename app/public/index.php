<?php
phpinfo();
require_once __DIR__ . '/../vendor/autoload.php';

#require_once __DIR__.'/../system/classes/core/Autoloader.php';
#spl_autoload_register('core\Autoloader::loader');

$app = new App\Core\Application('public');

$app->logger->info('Application is running');

echo '<pre>';
print_r($app->config->getAll());
echo '</pre>';
