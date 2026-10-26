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

    public static function updateBulkMode(string $mode): int {
        $pdo = \App\Core\Database::getConnection();
        if (!$pdo) return 0;

        $mode = ($mode === 'live') ? 'live' : 'sandbox';
        $stmt = $pdo->prepare("UPDATE `applications` SET `mode` = ?");
        $stmt->execute([$mode]);
        return $stmt->rowCount();
    }

    /**
     * Tentukan mode transaksi (live vs sandbox).
     * Jika client menyebutkan mode yang valid, gunakan mode client.
     * Jika tidak disebutkan di client, gunakan mode platform (default: sandbox).
     */
    public static function resolveMode(array $app, ?string $clientMode = null): string {
        if (!empty($clientMode)) {
            $normalized = GatewayConfig::normalizeMode($clientMode);
            if ($normalized !== null) {
                return $normalized;
            }
        }
        $appMode = GatewayConfig::normalizeMode($app['mode'] ?? 'sandbox', 'sandbox');
        return $appMode ?: 'sandbox';
    }

    /**
     * Tentukan gateway untuk platform.
     * Urutan: gateway khusus mode (gateway_live / gateway_sandbox) -> default_gateway
     * platform -> default global -> gateway aktif pertama.
     */
    public static function resolveGateway(array $app, ?string $mode = null): string {
        $mode = $mode ? GatewayConfig::normalizeMode($mode, 'sandbox') : self::resolveMode($app);
        $candidates = [];
        if ($mode === 'live') {
            $candidates[] = $app['gateway_live'] ?? 'default';
        } elseif ($mode === 'sandbox') {
            $candidates[] = $app['gateway_sandbox'] ?? 'default';
        }
        $candidates[] = $app['default_gateway'] ?? 'default';

        foreach ($candidates as $gw) {
            if (!empty($gw) && $gw !== 'default') {
                return $gw;
            }
        }

        $globalDefault = Setting::get('default_gateway', '');
        if (!empty($globalDefault) && in_array($globalDefault, ['midtrans', 'duitku', 'doku'])) {
            return $globalDefault;
        }

        $activeGws = GatewayConfig::getActiveGateways();
        return !empty($activeGws) ? $activeGws[0]['gateway_code'] : 'duitku';
    }
}
