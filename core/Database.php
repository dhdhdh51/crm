<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct() {
        $cfg = require ROOT . '/config/database.php';
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4";
        try {
            $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $e) {
            error_log('[DB] Connection failed: ' . $e->getMessage());
            http_response_code(503);
            die('Service temporarily unavailable.');
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }

    /** Execute a query and return the statement */
    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch all rows */
    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    /** Fetch single row */
    public function fetch(string $sql, array $params = []): array|false {
        return $this->query($sql, $params)->fetch();
    }

    /** Fetch single column value */
    public function fetchColumn(string $sql, array $params = []): mixed {
        return $this->query($sql, $params)->fetchColumn();
    }

    /** Insert and return last insert ID */
    public function insert(string $sql, array $params = []): string {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }

    /** Execute update/delete and return affected rows */
    public function execute(string $sql, array $params = []): int {
        return $this->query($sql, $params)->rowCount();
    }

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void          { $this->pdo->commit(); }
    public function rollback(): void        { $this->pdo->rollBack(); }

    // Prevent cloning
    private function __clone() {}
}
