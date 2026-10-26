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

            self::ensureSchema(self::$instance);
        }

        return self::$instance;
    }

    /**
     * Kolom tambahan yang wajib ada. Dicek otomatis saat koneksi pertama,
     * sehingga instalasi lama ter-update tanpa harus menjalankan migrate manual.
     */
    private static function schemaColumns(): array {
        return [
            ['applications', 'mode',            "ENUM('sandbox','live') NOT NULL DEFAULT 'sandbox' AFTER `default_gateway`"],
            ['applications', 'gateway_live',    "VARCHAR(50) NOT NULL DEFAULT 'default' AFTER `mode`"],
            ['applications', 'gateway_sandbox', "VARCHAR(50) NOT NULL DEFAULT 'default' AFTER `gateway_live`"],
            ['transactions', 'return_url',      "VARCHAR(255) DEFAULT NULL AFTER `fee`"],
            ['transactions', 'webhook_url',     "VARCHAR(255) DEFAULT NULL AFTER `return_url`"],
            ['transactions', 'mode',            "ENUM('sandbox','live') NOT NULL DEFAULT 'sandbox' AFTER `gateway`"],
        ];
    }

    public static function ensureSchema(PDO $pdo): void {
        try {
            if (!$pdo->query("SHOW TABLES LIKE 'transactions'")->fetch()) {
                return; // belum diinstall
            }

            foreach (self::schemaColumns() as [$table, $column, $definition]) {
                $exists = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->fetch();
                if ($exists) {
                    continue;
                }

                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");

                // Transaksi lama: samakan mode dengan environment gateway saat ini
                if ($table === 'transactions' && $column === 'mode') {
                    $pdo->exec("UPDATE `transactions` t JOIN `gateway_configs` g ON g.gateway_code = t.gateway
                                SET t.mode = IF(g.is_production = 1, 'live', 'sandbox')");
                }
            }
        } catch (\Throwable $e) {
            // Jangan hentikan aplikasi bila user DB tidak punya hak ALTER
            error_log('[BiruPay] ensureSchema: ' . $e->getMessage());
        }
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

            self::ensureSchema($pdo);

            return ['success' => true, 'message' => 'Migrasi dan inisialisasi database berhasil dijalankan.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Migration error: ' . $e->getMessage()];
        }
    }
}

