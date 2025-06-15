<?php

namespace App\Core;

use Exception;
use PDO;
use PDO\Pgsql;
use PDOException;

class Database
{
    private static $instance = null;
    public $pdo;
    private $host;
    private $dbname;
    private $user;
    private $password;
    private $port;

    /**
     * Privát konstruktor a Singleton minta miatt
     */
    private function __construct($host, $dbname, $user, $password, $port = 5432)
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
        $this->connect();
    }

    /**
     * Singleton példány lekérése
     * @param string|null $host     Adatbázis kiszolgáló címe.
     * @param integer     $port     Portszám (alapértelmezett: 5432).
     * @param string|null $database Adatbázis neve.
     * @param string|null $username Felhasználónév.
     * @param string|null $password Jelszó.
     * @return Database|null
     * @throws Exception Hibakezelés.
     */
    public static function getInstance(
        ?string $host = null,
        int $port = 5432,
        ?string $database = null,
        ?string $username = null,
        ?string $password = null
    ): ?Database {
        if (self::$instance === null) {
            if ($host === null || $database === null || $username === null || $password === null) {
                throw new Exception("Adatbázis konfiguráció megadása szükséges az első inicializáláskor.");
            }
            self::$instance = new self($host, $database, $username, $password, $port);
        }
        return self::$instance;
    }

    /**
     * Klónozás tiltása
     */
    private function __clone()
    {
    }

    /**
     * Szerializálás tiltása
     */
    public function __wakeup()
    {
        $this->connect();
    }

    /**
     * Adatbázis kapcsolat létrehozása PDO-val
     * @throws PDOException Ha a kapcsolat nem sikerül
     */
    private function connect()
    {
        try {

            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
            $this->pdo = new Pgsql($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new PDOException("Kapcsolódási hiba: " . $e->getMessage());
        }
    }

    /**
     * Tárolt függvény hívása JSON lista kimenettel és validációval
     * @param string $functionName Függvény neve
     * @param array  $params       Bemeneti paraméterek tömbje
     * @param array  $jsonSchema   Várt JSON oszlopok és típusok (pl. ['id' => 'integer', 'name' => 'string'])
     * @return array Validált JSON lista
     */
    public function callFunctionWithJsonList($functionName, array $params = [], array $jsonSchema = [])
    {
        try {
            // Helyőrzők generálása
            $placeholders = implode(',', array_fill(0, count($params), '?'));
            $query = "SELECT * FROM {$functionName}({$placeholders})";
            $stmt = $this->pdo->prepare($query);

            // Bemeneti paraméterek kötése
            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }

            // Lekérdezés végrehajtása
            $stmt->execute();
            $result = $stmt->fetch();

            // JSON dekódolás
            if (!$result || !isset($result['result'])) {
                throw new PDOException("A függvény nem adott vissza JSON-t.");
            }

            $jsonData = json_decode($result['result'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Érvénytelen JSON adat: " . json_last_error_msg());
            }

            // Ha nem tömb, akkor hiba
            if (!is_array($jsonData)) {
                throw new InvalidArgumentException("A függvény JSON kimenete nem lista: " . gettype($jsonData));
            }

            // Minden elem validálása
            $validatedData = [];
            foreach ($jsonData as $index => $row) {
                $validatedData[] = $this->validateJsonSchema($row, $jsonSchema, $functionName, $index);
            }

            return $validatedData;
        } catch (PDOException $e) {
            throw new PDOException("Függvény hívási hiba: " . $e->getMessage());
        }
    }

    /**
     * JSON struktúra validálása
     * @param array        $jsonData     Dekódolt JSON adat
     * @param array        $jsonSchema   Várt struktúra és típusok
     * @param string       $functionName Függvény neve a hibüzenethez
     * @param integer|null $rowIndex     Sor indexe (lista esetén)
     * @return array Validált adat
     * @throws InvalidArgumentException Ha a JSON nem felel meg a sémának
     */
    private function validateJsonSchema($jsonData, $jsonSchema, $functionName, $rowIndex = null)
    {
        if (empty($jsonSchema)) {
            return $jsonData; // Ha nincs séma, nem validálunk
        }

        // Ellenőrizzük, hogy minden várt kulcs megvan-e
        foreach ($jsonSchema as $key => $expectedType) {
            if (!array_key_exists($key, $jsonData)) {
                $errorMsg = "A(z) {$functionName} függvény JSON kimenete nem tartalmazza a(z) '{$key}' oszlopot";
                $errorMsg .= $rowIndex !== null ? " a(z) {$rowIndex}. sorban." : ".";
                throw new InvalidArgumentException($errorMsg);
            }

            $value = $jsonData[$key];
            switch (strtolower($expectedType)) {
                case 'integer':
                    if (!is_int($value) && (!is_numeric($value) || (int)$value != $value)) {
                        $errorMsg = "A(z) '{$key}' oszlopnak egész számnak kell lennie a(z) {$functionName} függvényben";
                        $errorMsg .= $rowIndex !== null ? " a(z) {$rowIndex}. sorban, kapott: " . gettype($value) : ", kapott: " . gettype($value);
                        throw new InvalidArgumentException($errorMsg);
                    }
                    $jsonData[$key] = (int)$value;
                    break;
                case 'float':
                    if (!is_float($value) && !is_numeric($value)) {
                        $errorMsg = "A(z) '{$key}' oszlopnak tizedes törtnek kell lennie a(z) {$functionName} függvényben";
                        $errorMsg .= $rowIndex !== null ? " a(z) {$rowIndex}. sorban, kapott: " . gettype($value) : ", kapott: " . gettype($value);
                        throw new InvalidArgumentException($errorMsg);
                    }
                    $jsonData[$key] = (float)$value;
                    break;
                case 'string':
                    if (!is_string($value)) {
                        $errorMsg = "A(z) '{$key}' oszlopnak szövegnek kell lennie a(z) {$functionName} függvényben";
                        $errorMsg .= $rowIndex !== null ? " a(z) {$rowIndex}. sorban, kapott: " . gettype($value) : ", kapott: " . gettype($value);
                        throw new InvalidArgumentException($errorMsg);
                    }
                    break;
                case 'boolean':
                    if (!is_bool($value)) {
                        $errorMsg = "A(z) '{$key}' oszlopnak logikai értéknek kell lennie a(z) {$functionName} függvényben";
                        $errorMsg .= $rowIndex !== null ? " a(z) {$rowIndex}. sorban, kapott: " . gettype($value) : ", kapott: " . gettype($value);
                        throw new InvalidArgumentException($errorMsg);
                    }
                    break;
                case 'array':
                    if (!is_array($value)) {
                        $errorMsg = "A(z) '{$key}' oszlopnak tömbnek kell lennie a(z) {$functionName} függvényben";
                        $errorMsg .= $rowIndex !== null ? " a(z) {$rowIndex}. sorban, kapott: " . gettype($value) : ", kapott: " . gettype($value);
                        throw new InvalidArgumentException($errorMsg);
                    }
                    break;
                default:
                    throw new InvalidArgumentException("Ismeretlen típus: {$expectedType} a(z) '{$key}' oszlophoz a(z) {$functionName} függvényben.");
            }
        }

        // Ellenőrizzük, hogy nincs-e extra oszlop
        foreach (array_keys($jsonData) as $key) {
            if (!array_key_exists($key, $jsonSchema)) {
                $errorMsg = "A(z) {$functionName} függvény JSON kimenete ismeretlen oszlopot tartalmaz: '{$key}'";
                $errorMsg .= $rowIndex !== null ? " a(z) {$rowIndex}. sorban." : ".";
                throw new InvalidArgumentException($errorMsg);
            }
        }

        return $jsonData;
    }

    /**
     * Tárolt eljárás hívása JSON kimenettel és validációval
     * @param string $procedureName Tárolt eljárás neve
     * @param array  $params        Bemeneti paraméterek tömbje
     * @param array  $jsonSchema    Várt JSON struktúra és típusok
     * @return array Validált JSON adatok
     */
    public function callProcedureWithJsonOutput($procedureName, array $params = [], array $jsonSchema = [])
    {
        try {
            $placeholders = implode(',', array_fill(0, count($params), '?'));
            $query = "SELECT * FROM {$procedureName}({$placeholders})";
            $stmt = $this->pdo->prepare($query);

            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }

            $stmt->execute();
            $result = $stmt->fetch();

            if (!$result || !isset($result['result'])) {
                throw new PDOException("A tárolt eljárás nem adott vissza JSON-t.");
            }

            $jsonData = json_decode($result['result'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Érvénytelen JSON adat: " . json_last_error_msg());
            }

            return $this->validateJsonSchema($jsonData, $jsonSchema, $procedureName);
        } catch (PDOException $e) {
            throw new PDOException("Tárolt eljárás hívási hiba: " . $e->getMessage());
        }
    }

    /**
     * Tárolt eljárás hívása paraméterekkel
     * @param string $procedureName Tárolt eljárás neve
     * @param array  $params        Paraméterek tömbje
     * @return array Eredmények tömbje
     */
    public function callProcedure($procedureName, array $params = [])
    {
        try {
            $placeholders = implode(',', array_fill(0, count($params), '?'));
            $query = "CALL {$procedureName} ({$placeholders})";
            $stmt = $this->pdo->prepare($query);

            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new PDOException("Tárolt eljárás hívási hiba: " . $e->getMessage());
        }
    }

    /**
     * Tárolt függvény hívása paraméterekkel, amely adatot ad vissza
     * @param string $functionName Függvény neve
     * @param array  $params       Paraméterek tömbje
     * @return array Eredmények tömbje
     */
    public function callFunction($functionName, array $params = [])
    {
        try {
            $placeholders = implode(',', array_fill(0, count($params), '?'));
            $query = "SELECT * FROM {$functionName}({$placeholders})";
            $stmt = $this->pdo->prepare($query);

            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new PDOException("Függvény hívási hiba: " . $e->getMessage());
        }
    }

    /**
     * Lista lekérése tárolt függvényen keresztül
     * @param string $functionName Lista lekérő függvény neve
     * @param array  $params       Paraméterek tömbje
     * @return array Eredmények tömbje
     */
    public function getList($functionName, array $params = [])
    {
        return $this->callFunction($functionName, $params);
    }

    /**
     * Egyetlen rekord lekérése tárolt függvényen keresztül
     * @param string $functionName Függvény neve
     * @param array  $params       Paraméterek tömbje
     * @return array|null Egy rekord vagy null, ha nincs találat
     */
    public function getSingle($functionName, array $params = [])
    {
        $result = $this->callFunction($functionName, $params);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Kapcsolat lezárása
     */
    public function close()
    {
        $this->pdo = null;
        self::$instance = null;
    }

    /**
     * Tranzakció indítása
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Tranzakció véglegesítése
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * Tranzakció visszavonása
     */
    public function rollback()
    {
        $this->pdo->rollBack();
    }
}
