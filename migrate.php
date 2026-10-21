<?php
/**
 * CLI Migration Runner for Biru Payment Hub
 * Usage: php migrate.php
 */

require_once __DIR__ . '/app/autoload.php';

use App\Core\Database;

echo "====================================================\n";
echo "  Biru Payment Hub - Database Migration Runner\n";
echo "====================================================\n\n";

if (!Database::isConnected()) {
    echo "ERROR: Koneksi database gagal!\n";
    echo "Pesan: " . (Database::getConnectionError() ?? 'Unknown') . "\n";
    echo "Silakan periksa konfigurasi di config/database.php atau .env\n";
    exit(1);
}

echo "Terhubung ke database.\n";
echo "Menjalankan migrasi schema dan data seed...\n";

$result = Database::migrateAndSeed();

if ($result['success']) {
    echo "SUKSES: " . $result['message'] . "\n";
    echo "\nUser Default:\n";
    echo "- Username: admin\n";
    echo "- Password: Password123!\n";
    echo "- Email: admin@biru.co.id\n";
    exit(0);
} else {
    echo "GAGAL: " . $result['message'] . "\n";
    exit(1);
}

