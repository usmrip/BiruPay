<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\View;
use App\Models\Application;
use App\Models\Transaction;
use App\Models\GatewayConfig;
use App\Gateways\MidtransGateway;
use App\Gateways\DuitkuGateway;
use App\Gateways\DokuGateway;
use App\Models\Log;

class ApiController extends Controller {
    private ?array $authenticatedApp = null;

    private function authenticate(Request $request): ?array {
        $apiKey = $request->header('X-Biru-ApiKey') ?: $request->input('api_key');
        if (empty($apiKey)) {
            return null;
        }

        $app = Application::findByApiKey($apiKey);
        if ($app && $app['status'] === 'active') {
            $this->authenticatedApp = $app;
            return $app;
        }

        return null;
    }

    public function createPayment(Request $request): void {
        $app = $this->authenticate($request);
        if (!$app) {
            $this->json([
                'status' => 'error',
                'message' => 'Autentikasi gagal: API Key tidak valid atau aplikasi tidak aktif.'
            ], 401);
            return;
        }

        $orderId       = trim((string) $request->input('order_id', ''));
        $amount        = (float) $request->input('amount', 0);
        $customerName  = trim((string) $request->input('customer_name', ''));
        $customerEmail = trim((string) $request->input('customer_email', ''));
        $customerPhone = trim((string) $request->input('customer_phone', ''));
        $gatewayChoice = strtolower(trim((string) $request->input('gateway', '')));
        $itemDetails   = $request->input('item_details', []);
        $itemName      = trim((string) $request->input('item_name', 'Pembayaran Layanan ' . $app['app_name']));

        if (empty($orderId) || $amount <= 0) {
            $this->json([
                'status' => 'error',
                'message' => 'Parameter order_id dan amount (> 0) wajib diisi.'
            ], 422);
            return;
        }

        // Check if an order already exists for this app
        $existing = Transaction::findByOrderIdAndApp($orderId, $app['id']);
        if ($existing && $existing['status'] === 'success') {
            $this->json([
                'status' => 'error',
                'message' => 'Order ID ini sudah pernah dibayar lunas.'
            ], 400);
            return;
        }

        // Generate unique hub reference
        $trxRef = Transaction::generateReference($app['app_code']);

        // Gateway resolution hierarchy: 1. Request parameter, 2. Platform/Application default, 3. Global system default
        $appDefaultGw = Application::resolveGateway($app);
        $selectedGateway = (!empty($gatewayChoice) && in_array($gatewayChoice, ['midtrans', 'duitku', 'doku'])) ? $gatewayChoice : $appDefaultGw;

        $trxData = [
            'app_id'          => $app['id'],
            'order_id'        => $orderId,
            'trx_reference'   => $trxRef,
            'gateway'         => $selectedGateway,
            'amount'          => $amount,
            'fee'             => 0.00,
            'customer_name'   => $customerName,
            'customer_email'  => $customerEmail,
            'customer_phone'  => $customerPhone,
            'item_details'    => !empty($itemDetails) ? json_encode($itemDetails) : null,
            'status'          => 'pending',
            'created_at'      => date('Y-m-d H:i:s'),
        ];

        $trxId = Transaction::create($trxData);
        if (!$trxId) {
            $this->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan transaksi ke database.'
            ], 500);
            return;
        }

        // Pre-initialize gateway token if possible
        $gatewayResponse = null;
        $params = [
            'trx_reference'   => $trxRef,
            'order_id'        => $orderId,
            'amount'          => $amount,
            'customer_name'   => $customerName,
            'customer_email'  => $customerEmail,
            'customer_phone'  => $customerPhone,
            'app_id'          => $app['id'],
            'app_code'        => $app['app_code'],
            'item_name'       => $itemName,
            'item_details'    => $itemDetails,
        ];

        if ($selectedGateway === 'midtrans') {
            $gw = new MidtransGateway();
            if ($gw->isConfigured()) {
                $gatewayResponse = $gw->createPayment($params);
                if (!empty($gatewayResponse['snap_token'])) {
                    Transaction::update($trxId, ['snap_token' => $gatewayResponse['snap_token']]);
                }
            }
        } elseif ($selectedGateway === 'duitku') {
            $gw = new DuitkuGateway();
            if ($gw->isConfigured()) {
                $gatewayResponse = $gw->createPayment($params);
                if (!empty($gatewayResponse['reference'])) {
                    Transaction::update($trxId, ['gateway_reference' => $gatewayResponse['reference']]);
                }
            }
        } elseif ($selectedGateway === 'doku') {
            $gw = new DokuGateway();
            if ($gw->isConfigured()) {
                $gatewayResponse = $gw->createPayment($params);
            }
        }

        // If the gateway explicitly failed (e.g. gateway server down, bad credentials)
        if ($gatewayResponse !== null && empty($gatewayResponse['success'])) {
            $errMsg = $gatewayResponse['message'] ?? 'Gagal membuat tagihan di payment gateway (' . strtoupper($selectedGateway) . ').';
            Log::error('api', "Payment gateway creation failed: {$errMsg}", ['gateway' => $selectedGateway, 'trx_reference' => $trxRef]);
            Transaction::update($trxId, ['status' => 'failed']);
            $this->json([
                'status' => 'error',
                'message' => $errMsg
            ], 502);
            return;
        }

        $paymentUrl = View::url('pay?ref=' . urlencode($trxRef));

        Log::info('api', "Payment created for app {$app['app_code']}, Order: {$orderId}, Ref: {$trxRef}");

        $this->json([
            'status' => 'success',
            'message' => 'Transaksi pembayaran berhasil dibuat.',
            'data' => [
                'trx_reference'       => $trxRef,
                'order_id'            => $orderId,
                'amount'              => $amount,
                'gateway'             => $selectedGateway,
                'mode'                => $gatewayResponse['mode'] ?? 'sandbox',
                'script_url'          => $gatewayResponse['script_url'] ?? '',
                'payment_url'         => $paymentUrl,
                'gateway_payment_url' => $gatewayResponse['payment_url'] ?? null,
                'snap_token'          => $gatewayResponse['snap_token'] ?? null,
                'gateway_reference'   => $gatewayResponse['reference'] ?? null,
                'gateway_data'        => $gatewayResponse,
                'created_at'          => date('Y-m-d H:i:s')
            ]
        ], 201);
    }

    public function checkStatus(Request $request, string $orderId): void {
        $app = $this->authenticate($request);
        if (!$app) {
            $this->json(['status' => 'error', 'message' => 'Autentikasi gagal.'], 401);
            return;
        }

        $trx = Transaction::findByOrderIdAndApp($orderId, $app['id']);
        if (!$trx) {
            // Also attempt lookup by hub trx_reference
            $trx = Transaction::findByReference($orderId);
            if ($trx && (int)$trx['app_id'] !== (int)$app['id']) {
                $trx = null;
            }
        }

        if (!$trx) {
            $this->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan.'], 404);
            return;
        }

        // Real-time synchronization with payment gateway if status is still pending
        if ($trx['status'] === 'pending') {
            $trx = \App\Services\PaymentSyncService::syncTransaction($trx);
        }

        $this->json([
            'status' => 'success',
            'data' => [
                'order_id'         => $trx['order_id'],
                'trx_reference'    => $trx['trx_reference'],
                'gateway'          => $trx['gateway'],
                'payment_method'   => $trx['payment_method'],
                'amount'           => (float) $trx['amount'],
                'status'           => $trx['status'],
                'is_app_notified'  => (bool) $trx['is_app_notified'],
                'paid_at'          => $trx['paid_at'],
                'created_at'       => $trx['created_at'],
            ]
        ]);
    }

    public function getGateways(Request $request): void {
        $gateways = GatewayConfig::getActiveGateways();
        $list = array_map(function($g) {
            return [
                'code' => $g['gateway_code'],
                'name' => $g['gateway_name'],
                'mode' => $g['is_production'] ? 'production' : 'sandbox',
            ];
        }, $gateways);

        $this->json([
            'status' => 'success',
            'data' => $list
        ]);
    }
}

