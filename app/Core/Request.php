<?php
namespace App\Core;

class Request {
    private array $get;
    private array $post;
    private array $server;
    private ?array $json = null;

    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
    }

    public function method(): string {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool {
        return $this->method() === 'POST';
    }

    public function isGet(): bool {
        return $this->method() === 'GET';
    }

    public function get(string $key, $default = null) {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null) {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, $default = null) {
        $json = $this->json();
        if ($json !== null && array_key_exists($key, $json)) {
            return $json[$key];
        }
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array {
        $json = $this->json();
        if ($json !== null) {
            return array_merge($this->get, $json);
        }
        return array_merge($this->get, $this->post);
    }

    public function json(): ?array {
        if ($this->json === null) {
            $input = file_get_contents('php://input');
            if (!empty($input)) {
                $decoded = json_decode($input, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->json = $decoded;
                }
            }
        }
        return $this->json;
    }

    public function rawBody(): string {
        return file_get_contents('php://input') ?: '';
    }

    public function header(string $name, $default = null) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$key] ?? $default;
    }

    public function isAjax(): bool {
        return ($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest' ||
               str_contains($this->server['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public function ip(): string {
        return $this->server['HTTP_CF_CONNECTING_IP'] ?? 
               $this->server['HTTP_X_FORWARDED_FOR'] ?? 
               $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}

