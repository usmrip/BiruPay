<?php
namespace App\Models;

use App\Core\Model;

class GatewayConfig extends Model {
    protected static string $table = 'gateway_configs';

    /**
     * Normalisasi mode dari client. Kosong => $default (sandbox).
     * Mengembalikan 'live' | 'sandbox', atau null bila nilainya tidak dikenal.
     */
    public static function normalizeMode($mode, string $default = 'sandbox'): ?string {
        $mode = strtolower(trim((string) $mode));
        if ($mode === '') return $default;
        if (in_array($mode, ['live', 'production', 'prod'], true)) return 'live';
        if (in_array($mode, ['sandbox', 'test', 'testing', 'dev'], true)) return 'sandbox';
        return null;
    }

    /**
     * Ambil konfigurasi gateway. Jika $mode diberikan ('live'|'sandbox'), kredensial
     * yang dipakai adalah kredensial mode tersebut (tanpa fallback ke mode lain),
     * terlepas dari saklar environment global gateway.
     */
    public static function getByCode(string $code, ?string $mode = null): ?array {
        $row = self::findBy('gateway_code', $code);
        if (!$row) return null;

        $mode = $mode !== null ? self::normalizeMode($mode) : null;
        if ($mode !== null) {
            $row['is_production'] = $mode === 'live' ? 1 : 0;
            $row['__strict_mode'] = true;
        }

        return self::formatGatewayRow($row);
    }

    public static function getActiveGateways(): array {
        $rows = self::where(['is_active' => 1], 'id ASC');
        foreach ($rows as &$row) {
            $row = self::formatGatewayRow($row);
        }
        return $rows;
    }

    public static function formatGatewayRow(array $row): array {
        if (!empty($row['additional_config'])) {
            $row['additional'] = json_decode($row['additional_config'], true) ?? [];
        } else {
            $row['additional'] = [];
        }

        $isProd = !empty($row['is_production']);
        $row['mode'] = $isProd ? 'production' : 'sandbox';
        $strict = !empty($row['__strict_mode']);
        unset($row['__strict_mode']);

        if ($strict) {
            // Mode ditentukan client: pakai kredensial mode itu saja
            $p = $isProd ? 'production_' : 'sandbox_';
            $row['active_server_key']  = $row[$p . 'server_key']  ?? '';
            $row['active_client_key']  = $row[$p . 'client_key']  ?? '';
            $row['active_merchant_id'] = $row[$p . 'merchant_id'] ?? '';
        } elseif ($isProd) {
            $row['active_server_key']  = !empty($row['production_server_key']) ? $row['production_server_key'] : ($row['server_key'] ?? '');
            $row['active_client_key']  = !empty($row['production_client_key']) ? $row['production_client_key'] : ($row['client_key'] ?? '');
            $row['active_merchant_id'] = !empty($row['production_merchant_id']) ? $row['production_merchant_id'] : ($row['merchant_id'] ?? '');
        } else {
            $row['active_server_key']  = !empty($row['sandbox_server_key']) ? $row['sandbox_server_key'] : ($row['server_key'] ?? '');
            $row['active_client_key']  = !empty($row['sandbox_client_key']) ? $row['sandbox_client_key'] : ($row['client_key'] ?? '');
            $row['active_merchant_id'] = !empty($row['sandbox_merchant_id']) ? $row['sandbox_merchant_id'] : ($row['merchant_id'] ?? '');
        }

        // Backward compatibility for existing gateway drivers
        $row['server_key']  = $row['active_server_key'];
        $row['client_key']  = $row['active_client_key'];
        $row['merchant_id'] = $row['active_merchant_id'];

        return $row;
    }

    public static function saveGateway(string $code, array $data): bool {
        $existing = self::findBy('gateway_code', $code);
        if (!$existing) {
            return false;
        }

        return self::update($existing['id'], $data);
    }
}
