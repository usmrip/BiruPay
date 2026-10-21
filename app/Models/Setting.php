<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Setting extends Model {
    protected static string $table = 'settings';
    private static array $cache = [];

    public static function get(string $key, $default = null): ?string {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $row = self::findBy('setting_key', $key);
        if ($row) {
            self::$cache[$key] = $row['setting_value'];
            return $row['setting_value'];
        }

        return $default;
    }

    public static function set(string $key, $value): bool {
        self::$cache[$key] = (string)$value;
        $pdo = Database::getConnection();
        if (!$pdo) return false;

        $sql = "INSERT INTO `settings` (`setting_key`, `setting_value`) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$key, (string)$value]);
    }

    public static function getAll(): array {
        $rows = self::all('setting_key ASC');
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }
}

