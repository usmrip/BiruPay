<?php
namespace App\Models;

use App\Core\Model;

class Log extends Model {
    protected static string $table = 'logs';

    public static function write(string $channel, string $level, string $message, array $context = []): void {
        try {
            self::create([
                'channel' => $channel,
                'level' => $level,
                'message' => $message,
                'context' => !empty($context) ? json_encode($context) : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            // Fail silently if DB is unreachable to prevent breaking main flows
            error_log("Payment Hub Log Error: " . $e->getMessage());
        }
    }

    public static function info(string $channel, string $message, array $context = []): void {
        self::write($channel, 'info', $message, $context);
    }

    public static function error(string $channel, string $message, array $context = []): void {
        self::write($channel, 'error', $message, $context);
    }

    public static function warning(string $channel, string $message, array $context = []): void {
        self::write($channel, 'warning', $message, $context);
    }
}

