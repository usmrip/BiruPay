<?php
namespace App\Core;

abstract class Controller {
    protected function render(string $view, array $data = [], ?string $layout = 'admin'): void {
        View::render($view, $data, $layout);
    }

    protected function json(array $data, int $status = 200): void {
        Response::json($data, $status);
    }

    protected function redirect(string $path, int $status = 302): void {
        Response::redirect(View::url($path), $status);
    }

    protected function back(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? View::url('dashboard');
        Response::redirect($referer);
    }

    protected function validateCsrf(Request $request): void {
        $token = $request->input('_csrf_token');
        if (!Session::validateCsrf($token)) {
            Session::setFlash('danger', 'Sesi kadaluarsa atau token keamanan tidak valid. Silakan coba lagi.');
            $this->back();
            exit;
        }
    }
}

