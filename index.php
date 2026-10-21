<?php
/**
 * Biru Payment Hub - Single Entry Point
 * Biru Indonesia Creative
 */

require_once __DIR__ . '/app/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\Database;
use App\Core\View;

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\GatewayController;
use App\Controllers\ApplicationController;
use App\Controllers\TransactionController;
use App\Controllers\UserController;
use App\Controllers\SettingController;
use App\Controllers\PaymentController;
use App\Controllers\ApiController;
use App\Controllers\WebhookController;
use App\Controllers\DocsController;
use App\Controllers\InstallController;

// Start session
Session::start();

// Initialize request
$request = new Request();
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Global CORS handler for API and SDK endpoints (Allows cross-origin calls from formulir.vip etc.)
if (str_contains($currentUri, '/api/') || str_contains($currentUri, 'birupay.js')) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Biru-ApiKey, Accept, X-Requested-With');
    header('Access-Control-Max-Age: 86400');

    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// Database check: if not installed and not accessing install or api, redirect to install
if (!Database::isInstalled()) {
    if (str_starts_with($currentUri, '/api/')) {
        Response::json([
            'status' => 'error',
            'message' => 'Database belum terpasang. Silakan lakukan setup melalui halaman /install'
        ], 503);
    }

    $routeParam = $_GET['route'] ?? '';
    $isInstallPage = str_contains($currentUri, '/install') || $routeParam === 'install' || str_starts_with($routeParam, 'install/');
    $isAsset = str_contains($currentUri, '/public/assets/') || str_starts_with($routeParam, 'public/assets/');

    if (!$isInstallPage && !$isAsset) {
        Response::redirect(View::url('install'));
    }
}

// ----------------- ROUTES ----------------- //

// Installation wizard
Router::get('/install', [InstallController::class, 'index']);
Router::post('/install', [InstallController::class, 'process']);

// Auth Routes
Router::get('/login', [AuthController::class, 'showLogin'], ['guest']);
Router::post('/login', [AuthController::class, 'login'], ['guest']);
Router::get('/logout', [AuthController::class, 'logout']);

// Dashboard
Router::get('/', function() {
    if (Session::has('user_id')) {
        Response::redirect(View::url('dashboard'));
    } else {
        Response::redirect(View::url('login'));
    }
});
Router::get('/dashboard', [DashboardController::class, 'index'], ['auth']);

// Gateway Setup
Router::get('/gateways', [GatewayController::class, 'index'], ['auth']);
Router::post('/gateways/{code}', [GatewayController::class, 'update'], ['auth']);

// Applications & Platforms
Router::get('/applications', [ApplicationController::class, 'index'], ['auth']);
Router::get('/applications/create', [ApplicationController::class, 'create'], ['auth']);
Router::post('/applications', [ApplicationController::class, 'store'], ['auth']);
Router::post('/applications/bulk-gateway', [ApplicationController::class, 'bulkGateway'], ['auth']);
Router::post('/applications/global-default', [ApplicationController::class, 'setGlobalDefaultGateway'], ['auth']);
Router::get('/applications/{id}/edit', [ApplicationController::class, 'edit'], ['auth']);
Router::post('/applications/{id}', [ApplicationController::class, 'update'], ['auth']);
Router::post('/applications/{id}/quick-gateway', [ApplicationController::class, 'quickSetGateway'], ['auth']);
Router::post('/applications/{id}/regenerate-key', [ApplicationController::class, 'regenerateKey'], ['auth']);
Router::post('/applications/{id}/delete', [ApplicationController::class, 'delete'], ['auth']);

// Transactions & Reports
Router::get('/transactions', [TransactionController::class, 'index'], ['auth']);
Router::get('/transactions/export', [TransactionController::class, 'exportCsv'], ['auth']);
Router::get('/transactions/{id}', [TransactionController::class, 'show'], ['auth']);
Router::post('/transactions/{id}/resend-webhook', [TransactionController::class, 'resendWebhook'], ['auth']);
Router::post('/transactions/{id}/mark-as-paid', [TransactionController::class, 'markAsPaid'], ['auth']);

// User Manager
Router::get('/users', [UserController::class, 'index'], ['auth']);
Router::get('/users/create', [UserController::class, 'create'], ['auth']);
Router::post('/users', [UserController::class, 'store'], ['auth']);
Router::get('/users/{id}/edit', [UserController::class, 'edit'], ['auth']);
Router::post('/users/{id}', [UserController::class, 'update'], ['auth']);
Router::post('/users/{id}/delete', [UserController::class, 'delete'], ['auth']);

// System & Notification Settings
Router::get('/settings', [SettingController::class, 'index'], ['auth']);
Router::post('/settings', [SettingController::class, 'update'], ['auth']);
Router::post('/settings/test-telegram', [SettingController::class, 'testTelegram'], ['auth']);
Router::post('/settings/test-email', [SettingController::class, 'testEmail'], ['auth']);

// Integration Documentation
Router::get('/docs', [DocsController::class, 'index'], ['auth']);

// Public SDK & Composer Distribution
Router::get('/birupay.js', function() {
    header('Content-Type: application/javascript; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: public, max-age=3600');
    readfile(__DIR__ . '/public/birupay.js');
    exit;
});
Router::get('/sdk/BiruPay.php', function() {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="BiruPay.php"');
    readfile(__DIR__ . '/sdk/BiruPay.php');
    exit;
});
Router::get('/packages.json', function() {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    readfile(__DIR__ . '/composer/packages.json');
    exit;
});
Router::get('/composer/packages.json', function() {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    readfile(__DIR__ . '/composer/packages.json');
    exit;
});
Router::get('/composer/{file}', function(Request $request, $file) {
    $filePath = __DIR__ . '/composer/' . basename((string)$file);
    if (file_exists($filePath)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename((string)$file) . '"');
        readfile($filePath);
        exit;
    }
    http_response_code(404);
    echo "File not found";
    exit;
});

// Customer Payment Checkout & Return
Router::get('/pay', [PaymentController::class, 'checkout']);
Router::post('/payment/initiate', [PaymentController::class, 'initiateGateway']);
Router::get('/payment/return', [PaymentController::class, 'paymentReturn']);

// Webhook Notifications from Gateways
Router::post('/webhook/midtrans', [WebhookController::class, 'midtrans']);
Router::post('/webhook/duitku', [WebhookController::class, 'duitku']);
Router::post('/webhook/doku', [WebhookController::class, 'doku']);

// Public REST API for Biru Platforms
Router::post('/api/v1/payment/create', [ApiController::class, 'createPayment']);
Router::get('/api/v1/payment/status/{orderId}', [ApiController::class, 'checkStatus']);
Router::get('/api/v1/gateways', [ApiController::class, 'getGateways']);

// Dispatch
Router::dispatch($request);
