<?php
namespace App\Models;

use App\Core\Model;

class Application extends Model {
    protected static string $table = 'applications';

    public static function generateApiKey(): string {
        return 'biru_' . bin2hex(random_bytes(24));
    }

    public static function findByApiKey(string $apiKey): ?array {
        return self::findBy('api_key', $apiKey);
    }

    public static function findByCode(string $code): ?array {
        return self::findBy('app_code', $code);
    }

    public static function updateBulkGateway(string $gateway): int {
        $pdo = \App\Core\Database::getConnection();
        if (!$pdo) return 0;

        $stmt = $pdo->prepare("UPDATE `applications` SET `default_gateway` = ?");
        $stmt->execute([$gateway]);
        return $stmt->rowCount();
    }

    public static function resolveGateway(array $app): string {
        $appGw = $app['default_gateway'] ?? 'default';
        if (!empty($appGw) && $appGw !== 'default') {
            return $appGw;
        }

        $globalDefault = Setting::get('default_gateway', '');
        if (!empty($globalDefault) && in_array($globalDefault, ['midtrans', 'duitku', 'doku'])) {
            return $globalDefault;
        }

        $activeGws = GatewayConfig::getActiveGateways();
        return !empty($activeGws) ? $activeGws[0]['gateway_code'] : 'duitku';
    }
}

