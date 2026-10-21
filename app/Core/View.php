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
}

