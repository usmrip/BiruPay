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

    public static function updateBulkGateway(string $gateway, string $mode = 'all'): int {
        $pdo = \App\Core\Database::getConnection();
        if (!$pdo) return 0;

        if ($mode === 'live') {
            $stmt = $pdo->prepare("UPDATE `applications` SET `gateway_live` = ?");
            $stmt->execute([$gateway]);
        } elseif ($mode === 'sandbox') {
            $stmt = $pdo->prepare("UPDATE `applications` SET `gateway_sandbox` = ?");
            $stmt->execute([$gateway]);
        } else {
            $stmt = $pdo->prepare("UPDATE `applications` SET `default_gateway` = ?, `gateway_live` = ?, `gateway_sandbox` = ?");
            $stmt->execute([$gateway, $gateway, $gateway]);
        }
        return $stmt->rowCount();
    }

    /**
     * Tentukan gateway untuk platform berdasarkan mode transaksi.
     * Jika client tidak menyebutkan mode, default adalah 'sandbox'.
     * Urutan resolusi:
     * 1. Mode Live    => gateway_live platform -> default_gateway platform -> default global sistem -> gateway aktif pertama.
     * 2. Mode Sandbox => gateway_sandbox platform -> default_gateway platform -> default global sistem -> gateway aktif pertama.
     */
    public static function resolveGateway(array $app, ?string $mode = null): string {
        $mode = GatewayConfig::normalizeMode($mode, 'sandbox');
        $candidates = [];
        if ($mode === 'live') {
            $candidates[] = $app['gateway_live'] ?? 'default';
        } else {
            $candidates[] = $app['gateway_sandbox'] ?? 'default';
        }
        $candidates[] = $app['default_gateway'] ?? 'default';

        foreach ($candidates as $gw) {
            if (!empty($gw) && $gw !== 'default') {
                return $gw;
            }
        }

        $globalDefault = Setting::get('default_gateway', '');
        if (!empty($globalDefault) && in_array($globalDefault, ['midtrans', 'duitku', 'doku'], true)) {
            return $globalDefault;
        }

        $activeGws = GatewayConfig::getActiveGateways();
        return !empty($activeGws) ? $activeGws[0]['gateway_code'] : 'duitku';
    }
}
