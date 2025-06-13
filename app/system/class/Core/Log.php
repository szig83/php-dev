<?php

namespace App\Core;

use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Handler\RotatingFileHandler;

/**
 * Naplózás osztály
 */
class Log
{

    /**
     * @var Log|null
     */
    private static ?self $instance = null;

    /**
     * @var Logger
     */
    public Logger $logger;

    /**
     * Osztály konstruktor
     * @param Config $config Konfigurációs objektum.
     */
    private function __construct(Config $config)
    {
        $this->logger = new Logger($config->get('app.context'));

        if (!$config->get('log.enabled')) {
            return;
        }

        $lineFormatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        );

        $logPathRoot = $config->get('log.path.root') . DIRECTORY_SEPARATOR;
        if (!file_exists($logPathRoot)) {
            mkdir($logPathRoot, 0755, true);
        }

        $this->addHandler(
            Level::Error,
            Level::Error,
            $logPathRoot . $config->get('filename.log.error'),
            $lineFormatter,
            $config->get('log.max_files.error', 14)
        );

        $this->addHandler(
            Level::Warning,
            Level::Warning,
            $logPathRoot . $config->get('filename.log.warning'),
            $lineFormatter,
            $config->get('log.max_files.warning', 14)
        );

        $this->addHandler(
            Level::Info,
            Level::Info,
            $logPathRoot . $config->get('filename.log.info'),
            $lineFormatter,
            $config->get('log.max_files.info', 14)
        );

        $this->addHandler(
            Level::Debug,
            Level::Debug,
            $logPathRoot . $config->get('filename.log.debug'),
            $lineFormatter,
            $config->get('log.max_files.debug', 7)
        );

        $this->logger->pushHandler(new ErrorLogHandler(
            ErrorLogHandler::OPERATING_SYSTEM,
            Level::Warning
        ));

        $this->logger->pushProcessor(new WebProcessor()); // HTTP kérés adatok
        $this->logger->pushProcessor(new MemoryUsageProcessor()); // Memóriahasználat
    }

    /**
     * Osztály instance betöltése
     * @param Config $config Konfigurációs objektum.
     * @return self
     */
    public static function getInstance(Config $config): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    /**
     * Hiba naplozó hozzáadása/beállítása
     * @param Level         $minLevel  Naplózás szint (minimum).
     * @param Level         $maxLevel  Naplózás szint (maximum).
     * @param string        $logPath   Napló fájl elerés.
     * @param LineFormatter $formatter Naplózás formátum.
     * @param integer       $maxFiles  Naplózás fájlok száma.
     * @return void
     */
    public function addHandler(
        Level $minLevel,
        Level $maxLevel,
        string $logPath,
        LineFormatter $formatter,
        int $maxFiles = 14
    ): void {
        $handlerBase = new RotatingFileHandler(
            $logPath,
            $maxFiles,
            $maxLevel
        );
        $handler = new FilterHandler($handlerBase, $minLevel, $maxLevel);
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);
    }
}
