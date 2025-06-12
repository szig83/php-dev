<?php

namespace App\Core;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;

/**
 * Az alkalmazás alap osztálya
 */
class Application
{
    /**
     * @var Config Az alkalmazás konfigurációja
     */
    public Config $config;

    /**
     * @var Logger Az alkalmazás naplózásos osztálya
     */
    public Logger $logger;

    /**
     * Osztály konstruktor
     * @param string|null $context Az alkalmazás kontextusa (public, admin, stb.).
     */
    public function __construct(?string $context = null)
    {
        $this->config = Config::getInstance($context);
        $this->initializeLogger();
    }

    private function initializeLogger(): void
    {
        // Logger létrehozása az alkalmazás nevével
        $this->logger = new Logger($this->config->get('app.name'));

        $this->logger->pushHandler(new StreamHandler(
            $this->config->get('path.server.log').DIRECTORY_SEPARATOR.'app.log',
            Level::Debug // DEBUG szinttől naplóz
        ));

        // Környezetfüggő handlerek hozzáadása
       /* if ($this->config['env'] === 'development') {
            // Fejlesztői környezet: részletes naplózás fájlba és konzolra
            $this->logger->pushHandler(new StreamHandler(
                $this->config['log_path'],
                Logger::DEBUG // DEBUG szinttől naplóz
            ));
            $this->logger->pushHandler(new ErrorLogHandler(
                ErrorLogHandler::OPERATING_SYSTEM,
                Logger::DEBUG
            ));
        } else {
            // Éles környezet: csak WARNING és felette naplóz
            $this->logger->pushHandler(new StreamHandler(
                $this->config['log_path'],
                Logger::WARNING
            ));

            // Kritikus hibák küldése Slackre
            if ($this->config['slack_webhook']) {
                $this->logger->pushHandler(new SlackWebhookHandler(
                    $this->config['slack_webhook'],
                    'critical-errors', // Slack csatorna
                    $this->config['app_name'], // Feladó neve
                    true, // Emoji használata
                    Logger::CRITICAL // Csak CRITICAL szinttől
                ));
            }
        }*/

        // Processzorok hozzáadása a naplóbejegyzések gazdagításához
        $this->logger->pushProcessor(new WebProcessor()); // HTTP kérés adatok
        $this->logger->pushProcessor(new MemoryUsageProcessor()); // Memóriahasználat
    }
}
