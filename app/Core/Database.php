<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;
    private static ?string $connectionError = null;

    public static function getConnection(): ?PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
                self::$connectionError = null;
            } catch (PDOException $e) {
                self::$connectionError = $e->getMessage();
                return null;
            }
        }

        return self::$instance;
    }

    public static function isConnected(): bool {
        return self::getConnection() !== null;
    }

    public static function getConnectionError(): ?string {
        self::getConnection();
        return self::$connectionError;
    }

    /**
     * Check if tables exist in the database
     */
    public static function isInstalled(): bool {
        $pdo = self::getConnection();
        if (!$pdo) {
            return false;
        }

        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            return (bool) $stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Run schema.sql and seed.sql
     */
    public static function migrateAndSeed(): array {
        $pdo = self::getConnection();
        if (!$pdo) {
            return ['success' => false, 'message' => 'Database connection failed: ' . self::$connectionError];
        }

        try {
            $schemaFile = __DIR__ . '/../../database/schema.sql';
            $seedFile   = __DIR__ . '/../../database/seed.sql';

            if (file_exists($schemaFile)) {
                $schemaSql = file_get_contents($schemaFile);
                $pdo->exec($schemaSql);
            }

            if (file_exists($seedFile)) {
                $seedSql = file_get_contents($seedFile);
                $pdo->exec($seedSql);
            }

            return ['success' => true, 'message' => 'Migrasi dan inisialisasi database berhasil dijalankan.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Migration error: ' . $e->getMessage()];
        }
    }
}

