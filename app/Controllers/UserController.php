<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\User;

class UserController extends Controller {
    public function index(Request $request): void {
        $users = User::all('id ASC');

        $this->render('users.index', [
            'pageTitle' => 'User Manager',
            'activeNav' => 'users',
            'users' => $users,
        ]);
    }

    public function create(Request $request): void {
        $this->render('users.form', [
            'pageTitle' => 'Tambah User Admin Baru',
            'activeNav' => 'users',
            'user' => null,
        ]);
    }

    public function store(Request $request): void {
        $this->validateCsrf($request);

        $name     = trim($request->post('name', ''));
        $username = trim($request->post('username', ''));
        $email    = trim($request->post('email', ''));
        $password = trim($request->post('password', ''));
        $role     = $request->post('role') === 'operator' ? 'operator' : 'admin';

        if (empty($name) || empty($username) || empty($email) || empty($password)) {
            Session::setFlash('danger', 'Semua kolom wajib diisi.');
            $this->back();
            return;
        }

        if (User::findBy('username', $username)) {
            Session::setFlash('danger', 'Username sudah terdaftar.');
            $this->back();
            return;
        }

        if (User::findBy('email', $email)) {
            Session::setFlash('danger', 'Email sudah terdaftar.');
            $this->back();
            return;
        }

        User::create([
            'name'     => $name,
            'username' => $username,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role'     => $role,
            'status'   => 'active'
        ]);

        Session::setFlash('success', "User '{$name}' berhasil ditambahkan!");
        $this->redirect('users');
    }

    public function edit(Request $request, $id): void {
        $user = User::find($id);
        if (!$user) {
            Session::setFlash('danger', 'User tidak ditemukan.');
            $this->redirect('users');
            return;
        }

        $this->render('users.form', [
            'pageTitle' => 'Edit User: ' . $user['name'],
            'activeNav' => 'users',
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id): void {
        $this->validateCsrf($request);

        $user = User::find($id);
        if (!$user) {
            Session::setFlash('danger', 'User tidak ditemukan.');
            $this->redirect('users');
            return;
        }

        $name     = trim($request->post('name', ''));
        $email    = trim($request->post('email', ''));
        $password = trim($request->post('password', ''));
        $role     = $request->post('role') === 'operator' ? 'operator' : 'admin';
        $status   = $request->post('status') === 'inactive' ? 'inactive' : 'active';

        if (empty($name) || empty($email)) {
            Session::setFlash('danger', 'Nama dan email wajib diisi.');
            $this->back();
            return;
        }

        $updateData = [
            'name'   => $name,
            'email'  => $email,
            'role'   => $role,
            'status' => $status
        ];

        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        User::update($id, $updateData);

        Session::setFlash('success', "Data user '{$name}' berhasil diperbarui!");
        $this->redirect('users');
    }

    public function delete(Request $request, $id): void {
        $this->validateCsrf($request);

        if ((int)$id === (int)Session::get('user_id')) {
            Session::setFlash('danger', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
            $this->redirect('users');
            return;
        }

        $user = User::find($id);
        if ($user) {
            User::delete($id);
            Session::setFlash('success', "User '{$user['name']}' berhasil dihapus.");
        }

        $this->redirect('users');
    }
}

