<?php
namespace Core;

abstract class Model {
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function find(int $id): array|false {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
    }

    public function findAll(string $where = '', array $params = [], string $orderBy = ''): array {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql, $params);
    }

    public function count(string $where = '', array $params = []): int {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        return (int)$this->db->fetchColumn($sql, $params);
    }

    public function insert(array $data): string {
        $keys   = array_keys($data);
        $cols   = implode(', ', array_map(fn($k) => "`{$k}`", $keys));
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));
        return $this->db->insert(
            "INSERT INTO `{$this->table}` ({$cols}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    public function update(int $id, array $data): int {
        $sets = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        return $this->db->execute(
            "UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = ?",
            [...array_values($data), $id]
        );
    }

    public function delete(int $id): int {
        return $this->db->execute(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Paginated query
     * Returns ['data' => [], 'total' => int, 'pages' => int]
     */
    public function paginate(
        int $page,
        int $perPage,
        string $select = '*',
        string $where = '',
        array $params = [],
        string $orderBy = 'id DESC'
    ): array {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) $countSql .= " WHERE {$where}";
        $total = (int)$this->db->fetchColumn($countSql, $params);

        $dataSql = "SELECT {$select} FROM `{$this->table}`";
        if ($where) $dataSql .= " WHERE {$where}";
        if ($orderBy) $dataSql .= " ORDER BY {$orderBy}";
        $dataSql .= " LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->db->fetchAll($dataSql, $params);

        return [
            'data'       => $data,
            'total'      => $total,
            'pages'      => max(1, (int)ceil($total / $perPage)),
            'currentPage'=> $page,
            'perPage'    => $perPage,
        ];
    }
}
