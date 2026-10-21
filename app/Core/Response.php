<?php
namespace App\Core;

class Response {
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $url, int $statusCode = 302): void {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }

    public static function html(string $content, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
        exit;
    }

    public static function notFound(string $message = 'Halaman tidak ditemukan'): void {
        http_response_code(404);
        echo "<!DOCTYPE html><html><head><title>404 Not Found</title><link rel='stylesheet' href='/pembayaran/public/assets/css/bootstrap.min.css'></head><body class='bg-light d-flex align-items-center min-vh-100'><div class='container text-center'><h1 class='display-1 fw-bold text-primary'>404</h1><h3 class='mb-3'>$message</h3><a href='/pembayaran' class='btn btn-primary'>Kembali ke Beranda</a></div></body></html>";
        exit;
    }

    public static function forbidden(string $message = 'Akses ditolak'): void {
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><title>403 Forbidden</title><link rel='stylesheet' href='/pembayaran/public/assets/css/bootstrap.min.css'></head><body class='bg-light d-flex align-items-center min-vh-100'><div class='container text-center'><h1 class='display-1 fw-bold text-danger'>403</h1><h3 class='mb-3'>$message</h3><a href='/pembayaran' class='btn btn-secondary'>Kembali ke Beranda</a></div></body></html>";
        exit;
    }
}

