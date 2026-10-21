<?php
namespace App\Core;

class View {
    private static string $viewsPath = __DIR__ . '/../../views';

    public static function render(string $view, array $data = [], ?string $layout = 'admin'): void {
        extract($data);

        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: $viewFile");
        }

        // Start output buffering for view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = self::$viewsPath . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout file not found: $layoutFile");
        }

        require $layoutFile;
    }

    public static function escape(?string $string): string {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function url(string $path = ''): string {
        $config = require __DIR__ . '/../../config/app.php';
        $base = rtrim($config['base_url'], '/');
        $path = ltrim($path, '/');
        return $path === '' ? $base : "$base/$path";
    }

    public static function asset(string $path): string {
        return self::url('public/assets/' . ltrim($path, '/'));
    }

    public static function csrfField(): string {
        $token = Session::csrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    public static function formatRupiah($amount): string {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }

    /**
     * Format timestamp menurut zona waktu platform/aplikasi (default UTC)
     */
    public static function formatDateTime(?string $dateTimeStr, ?string $timezone = 'UTC', string $format = 'd M Y, H:i T'): string {
        if (empty($dateTimeStr)) {
            return '-';
        }

        try {
            $dt = new \DateTime($dateTimeStr, new \DateTimeZone('UTC'));
            $tz = !empty($timezone) ? trim($timezone) : 'UTC';
            if (in_array($tz, \DateTimeZone::listIdentifiers())) {
                $dt->setTimezone(new \DateTimeZone($tz));
            }
            return $dt->format($format);
        } catch (\Throwable $e) {
            return $dateTimeStr;
        }
    }

    /**
     * Daftar pilihan zona waktu umum
     */
    public static function getTimezones(): array {
        return [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'Asia/Jakarta' => 'Asia/Jakarta (WIB, UTC+7)',
            'Asia/Makassar' => 'Asia/Makassar (WITA, UTC+8)',
            'Asia/Jayapura' => 'Asia/Jayapura (WIT, UTC+9)',
            'Asia/Singapore' => 'Asia/Singapore (SGT, UTC+8)',
            'Asia/Kuala_Lumpur' => 'Asia/Kuala Lumpur (MYT, UTC+8)',
            'Asia/Bangkok' => 'Asia/Bangkok (ICT, UTC+7)',
            'Asia/Tokyo' => 'Asia/Tokyo (JST, UTC+9)',
            'Europe/London' => 'Europe/London (GMT/BST)',
            'America/New_York' => 'America/New York (EST/EDT)',
            'America/Los_Angeles' => 'America/Los Angeles (PST/PDT)',
        ];
    }
}

