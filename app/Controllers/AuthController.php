<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;

class AuthController extends Controller {
    public function showLogin(Request $request): void {
        if (Session::has('user_id')) {
            $this->redirect('dashboard');
            return;
        }

        $this->render('auth.login', [
            'pageTitle' => 'Login Admin - Biru PayHub'
        ], 'auth');
    }

    public function login(Request $request): void {
        $this->validateCsrf($request);

        $username = trim($request->post('username', ''));
        $password = trim($request->post('password', ''));

        if (empty($username) || empty($password)) {
            Session::setFlash('danger', 'Username dan password wajib diisi.');
            $this->back();
            return;
        }

        $user = User::authenticate($username, $password);
        if (!$user) {
            Session::setFlash('danger', 'Kredensial login salah atau akun tidak aktif.');
            $this->back();
            return;
        }

        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);

        Session::setFlash('success', 'Selamat datang kembali, ' . htmlspecialchars($user['name']) . '!');
        $this->redirect('dashboard');
    }

    public function logout(Request $request): void {
        Session::destroy();
        $this->redirect('login');
    }
}

