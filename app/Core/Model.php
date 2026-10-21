<?php
namespace App\Core;

use PDO;

abstract class Model {
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function getTable(): string {
        return static::$table;
    }

    public static function all(string $orderBy = 'id DESC'): array {
        $pdo = Database::getConnection();
        if (!$pdo) return [];

        $table = static::$table;
        $stmt = $pdo->query("SELECT * FROM `{$table}` ORDER BY {$orderBy}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id): ?array {
        $pdo = Database::getConnection();
        if (!$pdo) return null;

        $table = static::$table;
        $pk = static::$primaryKey;
        $stmt = $pdo->prepare("SELECT * FROM `{$table}` WHERE `{$pk}` = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function findBy(string $column, $value): ?array {
        $pdo = Database::getConnection();
        if (!$pdo) return null;

        $table = static::$table;
        $stmt = $pdo->prepare("SELECT * FROM `{$table}` WHERE `{$column}` = ? LIMIT 1");
        $stmt->execute([$value]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function where(array $conditions, string $orderBy = 'id DESC', ?int $limit = null): array {
        $pdo = Database::getConnection();
        if (!$pdo) return [];

        $table = static::$table;
        $clauses = [];
        $values = [];

        foreach ($conditions as $column => $value) {
            $clauses[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $sql = "SELECT * FROM `{$table}`";
        if (!empty($clauses)) {
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }
        $sql .= " ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int|string|false {
        $pdo = Database::getConnection();
        if (!$pdo) return false;

        $table = static::$table;
        $columns = array_keys($data);
        $fields = '`' . implode('`, `', $columns) . '`';
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})";
        $stmt = $pdo->prepare($sql);
        $res = $stmt->execute(array_values($data));

        return $res ? $pdo->lastInsertId() : false;
    }

    public static function update($id, array $data): bool {
        $pdo = Database::getConnection();
        if (!$pdo) return false;

        $table = static::$table;
        $pk = static::$primaryKey;

        $setPart = [];
        $values = [];
        foreach ($data as $column => $value) {
            $setPart[] = "`{$column}` = ?";
            $values[] = $value;
        }
        $values[] = $id;

        $sql = "UPDATE `{$table}` SET " . implode(', ', $setPart) . " WHERE `{$pk}` = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public static function delete($id): bool {
        $pdo = Database::getConnection();
        if (!$pdo) return false;

        $table = static::$table;
        $pk = static::$primaryKey;
        $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE `{$pk}` = ?");
        return $stmt->execute([$id]);
    }

    public static function count(array $conditions = []): int {
        $pdo = Database::getConnection();
        if (!$pdo) return 0;

        $table = static::$table;
        $clauses = [];
        $values = [];

        foreach ($conditions as $column => $value) {
            $clauses[] = "`{$column}` = ?";
            $values[] = $value;
        }

        $sql = "SELECT COUNT(*) FROM `{$table}`";
        if (!empty($clauses)) {
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        return (int)$stmt->fetchColumn();
    }
}

