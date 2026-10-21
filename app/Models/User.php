<?php
namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected static string $table = 'users';

    public static function authenticate(string $usernameOrEmail, string $password): ?array {
        $user = self::findBy('username', $usernameOrEmail);
        if (!$user) {
            $user = self::findBy('email', $usernameOrEmail);
        }

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }
}

