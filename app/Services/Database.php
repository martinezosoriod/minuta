<?php

namespace App\Services;

use PDO;

/**
 * Clase Database - Singleton para conexiones seguras con PDO
 * Capa de infraestructura para acceso a datos
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;

    /**
     * Constructor privado para patrón Singleton
     */
    private function __construct()
    {
        $this->config = $this->loadConfig();
        $this->connect();
    }

    /**
     * Prevenir clonación del singleton
     */
    private function __clone() {}

    /**
     * Prevenir deserialización del singleton
     */
    public function __wakeup()
    {
        throw new \Exception("No se puede deserializar el singleton de Database");
    }

    /**
     * Obtener instancia única (Singleton)
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Cargar configuración desde archivo o variables de entorno
     */
    private function loadConfig(): array
    {
        $configFile = dirname(__DIR__, 2) . '/config/database.php';
        
        if (file_exists($configFile)) {
            return require $configFile;
        }

        // Configuración por defecto desde variables de entorno
        return [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_DATABASE') ?: 'minuta_electronica',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        ];
    }

    /**
     * Establecer conexión segura con PDO
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options'] ?? []
            );

        } catch (PDOException $e) {
            // Log error seguro sin exponer detalles sensibles
            error_log("Error de conexión a base de datos: " . $e->getMessage());
            throw new \RuntimeException('No se pudo establecer la conexión con la base de datos');
        }
    }

    /**
     * Obtener conexión PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Ejecutar consulta y retornar resultados
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en consulta: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ejecutar consulta y retornar una fila
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en consulta: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ejecutar comando (INSERT, UPDATE, DELETE)
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error en ejecución: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener último ID insertado
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollBack(): bool
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Verificar si hay transacción activa
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Cerrar conexión (para limpieza explícita)
     */
    public function disconnect(): void
    {
        $this->connection = null;
    }

    /**
     * Resetear instancia singleton (útil para testing)
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
