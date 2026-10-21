<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Core\View;
use PDO;
use PDOException;

class InstallController extends Controller {
    public function index(Request $request): void {
        $isConnected = Database::isConnected();
        $isInstalled = Database::isInstalled();
        $error = Database::getConnectionError();

        if ($isConnected && $isInstalled) {
            $this->redirect('login');
            return;
        }

        $this->render('install.index', [
            'pageTitle'   => 'Setup Database - Biru PayHub',
            'isConnected' => $isConnected,
            'isInstalled' => $isInstalled,
            'error'       => $error,
        ], 'auth');
    }

    public function process(Request $request): void {
        $dbHost = trim($request->post('db_host', '127.0.0.1'));
        $dbPort = trim($request->post('db_port', '3306'));
        $dbName = trim($request->post('db_name', 'biru_pembayaran'));
        $dbUser = trim($request->post('db_user', 'root'));
        $dbPass = trim($request->post('db_pass', ''));

        // Test PDO connection
        try {
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Save to .env file in root of pembayaran
            $envPath = __DIR__ . '/../../.env';
            $envContent = "# Database Configuration for Biru Payment Hub\n"
                        . "DB_HOST={$dbHost}\n"
                        . "DB_PORT={$dbPort}\n"
                        . "DB_DATABASE={$dbName}\n"
                        . "DB_USERNAME={$dbUser}\n"
                        . "DB_PASSWORD=\"{$dbPass}\"\n";

            file_put_contents($envPath, $envContent);

            // Execute schema and seed
            $schemaFile = __DIR__ . '/../../database/schema.sql';
            $seedFile   = __DIR__ . '/../../database/seed.sql';

            if (file_exists($schemaFile)) {
                $pdo->exec(file_get_contents($schemaFile));
            }

            if (file_exists($seedFile)) {
                $pdo->exec(file_get_contents($seedFile));
            }

            Session::setFlash('success', 'Database berhasil dikonfigurasi dan tabel telah dibuat! Silakan login dengan user: admin / Password: Password123!');
            $this->redirect('login');
            return;

        } catch (PDOException $e) {
            Session::setFlash('danger', 'Koneksi database gagal: ' . $e->getMessage());
            $this->back();
            return;
        }
    }
}

