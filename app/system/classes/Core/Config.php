<?php

namespace App\Core;

use Database;

class Config
{
    private static ?self $instance = null;
    private array $config = [];
    private string $defaultContext = 'public';
    private ?Database $db = null; // Adatbázis kapcsolat
    private array $dynamicValues = []; // Dinamikus értékek cache
    private bool $dbInitialized = false;

    private string $configPath;

    private function __construct(?string $context = null)
    {
        if ($context) {
            $this->defaultContext = $context;
        }
        $this->setConfigPath();
        $this->loadEnvironment();
        $this->loadConfigFiles();
    }

    /**
     * Alkalmazás gyökér elérésének beállítása a konfigurációs osztály számára.
     * Az elérhető path konfigurációk közé nem innen kerül be, ez csak az osztály számára szükséges a
     * további konfigurációk beolvasásához.
     * @return void
     */
    private function setConfigPath(): void
    {
        $serverDocumentRoot = $_SERVER["DOCUMENT_ROOT"] ?? null;
        if (!empty($serverDocumentRoot)) {
            $this->configPath = substr(
                $serverDocumentRoot,
                0,
                strripos($serverDocumentRoot, DIRECTORY_SEPARATOR)
            ) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'config';
        }
    }

    public static function getInstance(?string $context = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($context);
        }
        return self::$instance;
    }

    /**
     * .env fájl alapján beállítani a környezeti változókat (érzékeny adatok)
     * @return void
     */
    private function loadEnvironment(): void
    {
        $envPath = $this->configPath . DIRECTORY_SEPARATOR . '.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    putenv(sprintf('%s=%s', trim($key), trim($value)));
                }
            }
        }
    }

    /**
     * Kofigurációs fájlok betöltése
     * @return void
     */
    private function loadConfigFiles(): void
    {
        $env = getenv('APP_ENV') ?: 'prod';


        $files = glob($this->configPath . '/*.php');
        foreach ($files as $file) {
            if (basename($file) !== 'env') {
                $key = explode('_', basename($file, '.php'))[1];
                $loadedConfig = require $file;
                if (isset($this->config[$key]) && is_array($this->config[$key]) && is_array($loadedConfig)) {
                    $this->config[$key] = array_replace_recursive($this->config[$key], $loadedConfig);
                } else {
                    $this->config[$key] = $loadedConfig;
                }
            }
        }

        $envFile = $this->configPath . "/env/{$env}.php";
        if (file_exists($envFile)) {
            $envConfig = require $envFile;
            $this->mergeConfig($envConfig);
        }
    }

    private function mergeConfig(array $newConfig): void
    {
        $this->config = array_replace_recursive($this->config, $newConfig);
    }

    public function initializeDatabase(): void
    {
        if ($this->dbInitialized) {
            return;
        }

        // Adatbázis kapcsolat inicializálása
        $host = $this->get('database.host');
        $port = $this->get('database.port');
        $dbName = $this->get('database.database');
        $username = $this->get('database.username');
        $password = $this->get('database.password');

        try {
            // $this->db = new Database($host, $port, $dbName, $username, $password); // Feltételezett Database osztály
            $this->dbInitialized = true;
        } catch (\Exception $e) {
            error_log("Failed to initialize database: " . $e->getMessage());
            // Nem dobunk kivételt, hogy az alapértelmezett értékek használhatók legyenek
        }
    }

    public function get(string $key, mixed $default = null, ?string $context = null): mixed
    {
        $context = $context ?? $this->defaultContext;

        // Dinamikus értékek ellenőrzése (pl. app.theme)
        if ($this->isDynamicKey($key, $context)) {
            return $this->getDynamicValue($key, $context, $default);
        }

        // Statikus konfiguráció lekérése
        $keys = explode('.', $key);
        $current = $this->config;

        if ($context && isset($current[$keys[0]][$context])) {
            $current = $current[$keys[0]][$context];
            array_shift($keys);
            foreach ($keys as $k) {
                if (!isset($current[$k])) {
                    return $this->getFromDefault($key, $default);
                }
                $current = $current[$k];
            }
            return $current;
        }

        return $this->getFromDefault($key, $default);
    }

    public function getAll(?string $context = null): array
    {
        $result = [];
        $defaultContextName = $context ?? $this->defaultContext;

        foreach ($this->config as $rootKey => $rootConfig) {
            // If the root config item itself is not an array, keep it as is.
            if (!is_array($rootConfig)) {
                $result[$rootKey] = $rootConfig;
                continue;
            }

            // Collect all children that are not the default context key itself.
            $mainChildren = array_filter($rootConfig, function ($key) use ($defaultContextName) {
                return $key !== $defaultContextName;
            }, ARRAY_FILTER_USE_KEY);

            $defaultContextArray = [];
            // Get the array for the default context, if it exists.
            if (isset($rootConfig[$defaultContextName]) && is_array($rootConfig[$defaultContextName])) {
                $defaultContextArray = $rootConfig[$defaultContextName];
            }

            // Merge main children with the default context's settings.
            // Values from defaultContextArray will overwrite mainChildren if keys conflict.
            $result[$rootKey] = array_merge($mainChildren, $defaultContextArray);
        }
        return $result;
    }

    private function getFromDefault(string $key, mixed $default): mixed
    {
        $keys = explode('.', $key);
        $current = $this->config;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                return $default;
            }
            $current = $current[$k];
        }

        return $current;
    }

    private function isDynamicKey(string $key, string $context): bool
    {
        // Definiáljuk, mely kulcsok dinamikusak (pl. app.theme)
        return $key === 'app.theme' && in_array($context, ['public', 'admin']);
    }

    private function getDynamicValue(string $key, string $context, mixed $default): mixed
    {
        // Cache ellenőrzése
        $cacheKey = "{$context}.{$key}";
        if (isset($this->dynamicValues[$cacheKey])) {
            return $this->dynamicValues[$cacheKey];
        }

        // Adatbázis inicializálása, ha még nem történt meg
        $this->initializeDatabase();

        if ($this->dbInitialized && $this->db) {
            try {
                $value = $this->db->queryValue(
                    'SELECT value FROM settings WHERE context = ? AND key_name = ?',
                    [$context, $key]
                );
                if ($value !== null) {
                    $this->dynamicValues[$cacheKey] = $value;
                    return $value;
                }
            } catch (\Exception $e) {
                error_log("Failed to fetch dynamic value for {$cacheKey}: " . $e->getMessage());
            }
        }

        // Visszaesés az alapértelmezett értékre
        return $this->getFromDefault($key, $default);
    }

    public function setDynamicValue(string $key, string $context, string $value): bool
    {
        // Adatbázis inicializálása
        $this->initializeDatabase();

        if ($this->dbInitialized && $this->db) {
            try {
                $this->db->execute(
                    'INSERT INTO settings (context, key_name, value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = ?',
                    [$context, $key, $value, $value]
                );
                // Cache frissítése
                $cacheKey = "{$context}.{$key}";
                $this->dynamicValues[$cacheKey] = $value;
                return true;
            } catch (\Exception $e) {
                error_log("Failed to set dynamic value for {$key}: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    public function setContext(string $context)
    {
        $this->defaultContext = $context;
    }

    private function __clone()
    {
    }
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
