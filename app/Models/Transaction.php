<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;

class Transaction extends Model {
    protected static string $table = 'transactions';

    public static function generateReference(string $appCode = 'BIRU'): string {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', $appCode));
        if (empty($prefix)) $prefix = 'BPAY';
        return substr($prefix, 0, 6) . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    public static function getWithApplication(int $id): ?array {
        $pdo = Database::getConnection();
        if (!$pdo) return null;

        $sql = "SELECT t.*, a.app_name, a.app_code, a.webhook_url, a.return_url, a.timezone 
                FROM `transactions` t
                LEFT JOIN `applications` a ON t.app_id = a.id
                WHERE t.id = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByReference(string $ref): ?array {
        $pdo = Database::getConnection();
        if (!$pdo) return null;

        $sql = "SELECT t.*, a.app_name, a.app_code, a.webhook_url, a.return_url, a.timezone 
                FROM `transactions` t
                LEFT JOIN `applications` a ON t.app_id = a.id
                WHERE t.trx_reference = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ref]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByOrderIdAndApp(string $orderId, int $appId): ?array {
        $pdo = Database::getConnection();
        if (!$pdo) return null;

        $sql = "SELECT * FROM `transactions` WHERE `order_id` = ? AND `app_id` = ? ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$orderId, $appId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function findByOrderId(string $orderId): ?array {
        $pdo = Database::getConnection();
        if (!$pdo) return null;

        $sql = "SELECT t.*, a.app_name, a.app_code, a.webhook_url, a.return_url, a.timezone 
                FROM `transactions` t
                LEFT JOIN `applications` a ON t.app_id = a.id
                WHERE t.order_id = ? ORDER BY t.id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function filter(array $filters = [], string $orderBy = 't.id DESC', int $limit = 50, int $offset = 0): array {
        $pdo = Database::getConnection();
        if (!$pdo) return [];

        $conditions = [];
        $params = [];

        if (!empty($filters['app_id'])) {
            $conditions[] = "t.app_id = ?";
            $params[] = $filters['app_id'];
        }

        if (!empty($filters['gateway'])) {
            $conditions[] = "t.gateway = ?";
            $params[] = $filters['gateway'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $conditions[] = "DATE(t.created_at) >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $conditions[] = "DATE(t.created_at) <= ?";
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $conditions[] = "(t.order_id LIKE ? OR t.trx_reference LIKE ? OR t.customer_name LIKE ? OR t.customer_email LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT t.*, a.app_name, a.app_code, a.timezone 
                FROM `transactions` t
                LEFT JOIN `applications` a ON t.app_id = a.id
                {$whereSql}
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countFiltered(array $filters = []): int {
        $pdo = Database::getConnection();
        if (!$pdo) return 0;

        $conditions = [];
        $params = [];

        if (!empty($filters['app_id'])) {
            $conditions[] = "t.app_id = ?";
            $params[] = $filters['app_id'];
        }

        if (!empty($filters['gateway'])) {
            $conditions[] = "t.gateway = ?";
            $params[] = $filters['gateway'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $conditions[] = "DATE(t.created_at) >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $conditions[] = "DATE(t.created_at) <= ?";
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $conditions[] = "(t.order_id LIKE ? OR t.trx_reference LIKE ? OR t.customer_name LIKE ? OR t.customer_email LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT COUNT(*) FROM `transactions` t {$whereSql}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function getDashboardStats(): array {
        $pdo = Database::getConnection();
        if (!$pdo) {
            return [
                'total_volume' => 0,
                'total_success' => 0,
                'total_pending' => 0,
                'total_failed' => 0,
                'today_volume' => 0,
                'today_success' => 0,
                'by_gateway' => [],
                'by_app' => []
            ];
        }

        // Summary counts and amounts
        $sql = "SELECT 
                    COUNT(*) as total_trx,
                    COALESCE(SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END), 0) as total_volume,
                    COUNT(CASE WHEN status = 'success' THEN 1 END) as total_success,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as total_pending,
                    COUNT(CASE WHEN status IN ('failed', 'expired', 'cancelled') THEN 1 END) as total_failed,
                    COALESCE(SUM(CASE WHEN status = 'success' AND DATE(created_at) = CURDATE() THEN amount ELSE 0 END), 0) as today_volume,
                    COUNT(CASE WHEN status = 'success' AND DATE(created_at) = CURDATE() THEN 1 END) as today_success
                FROM `transactions`";
        $stmt = $pdo->query($sql);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Breakdown by gateway
        $gwSql = "SELECT gateway, COUNT(*) as count, COALESCE(SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END), 0) as volume 
                  FROM `transactions` GROUP BY gateway";
        $gwStmt = $pdo->query($gwSql);
        $stats['by_gateway'] = $gwStmt->fetchAll(PDO::FETCH_ASSOC);

        // Breakdown by application
        $appSql = "SELECT a.app_name, COUNT(t.id) as count, COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as volume 
                   FROM `applications` a
                   LEFT JOIN `transactions` t ON a.id = t.app_id
                   GROUP BY a.id, a.app_name
                   ORDER BY volume DESC LIMIT 5";
        $appStmt = $pdo->query($appSql);
        $stats['by_app'] = $appStmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
}

