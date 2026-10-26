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
        $mode = GatewayConfig::normalizeMode($request->input('mode', ''), 'sandbox');
        if ($mode === null) {
            $this->json([
                'status'  => 'error',
                'message' => "Parameter mode tidak valid. Gunakan 'sandbox' atau 'live'."
            ], 422);
            return;
        }
        $returnUrlCustom  = trim((string) $request->input('return_url', ''));
        $webhookUrlCustom = trim((string) $request->input('webhook_url', ''));

        if (empty($orderId) || $amount <= 0) {
            $this->json([
                'status' => 'error',
                'message' => 'Parameter order_id dan amount (> 0) wajib diisi.'
            ], 422);
            return;
        }

        // Check if an order already exists for this app
        $existing = Transaction::findByOrderIdAndApp($orderId, $app['id'], $mode);
        if ($existing) {
            // Jika status masih pending, sinkronisasi status ke payment gateway
            if ($existing['status'] === 'pending') {
                $existing = \App\Services\PaymentSyncService::syncTransaction($existing);
            }

            // Kasus 1: Sudah Pernah Dibayar Lunas (Success)
            if ($existing && $existing['status'] === 'success') {
                $successUrl = $app['return_url'] ?? View::url('payment/success?ref=' . urlencode($existing['trx_reference']));
                $this->json([
                    'status'  => 'error',
                    'code'    => 'ALREADY_PAID',
                    'message' => 'Order ID ini sudah pernah dibayar lunas.',
                    'data'    => [
                        'mode'           => $existing['mode'] ?? $mode,
                        'order_id'       => $existing['order_id'],
                        'trx_reference'  => $existing['trx_reference'],
                        'amount'         => (float)$existing['amount'],
                        'status'         => 'success',
                        'paid_at'        => $existing['paid_at'],
                        'gateway'        => $existing['gateway'],
                        'payment_method' => $existing['payment_method'],
                        'success_url'    => $successUrl,
                    ]
                ], 400);
                return;
            }

            // Kasus 2: Masih Menunggu Pembayaran (Pending)
            if ($existing && $existing['status'] === 'pending') {
                $paymentUrl = View::url('pay?ref=' . urlencode($existing['trx_reference']));
                $this->json([
                    'status'  => 'success',
                    'code'    => 'ALREADY_PENDING',
                    'message' => 'Tagihan untuk Order ID ini sudah ada dan sedang menunggu pembayaran.',
                    'data'    => [
                        'trx_reference'       => $existing['trx_reference'],
                        'order_id'            => $existing['order_id'],
                        'amount'              => (float)$existing['amount'],
                        'gateway'             => $existing['gateway'],
                        'mode'                => $existing['mode'] ?? $mode,
                        'status'              => 'pending',
                        'snap_token'          => $existing['snap_token'],
                        'gateway_reference'   => $existing['gateway_reference'],
                        'payment_url'         => $paymentUrl,
                        'is_existing'         => true,
                        'created_at'          => $existing['created_at'],
                    ]
                ], 200);
                return;
            }
        }

        // Generate unique hub reference
        $trxRef = Transaction::generateReference($app['app_code']);

        // Gateway resolution hierarchy: 1. Request parameter, 2. Platform/Application default, 3. Global system default
        $appDefaultGw = Application::resolveGateway($app, $mode);
        $selectedGateway = (!empty($gatewayChoice) && in_array($gatewayChoice, ['midtrans', 'duitku', 'doku'])) ? $gatewayChoice : $appDefaultGw;

        $trxData = [
            'app_id'          => $app['id'],
            'order_id'        => $orderId,
            'trx_reference'   => $trxRef,
            'gateway'         => $selectedGateway,
            'mode'            => $mode,
            'amount'          => $amount,
            'fee'             => 0.00,
            'return_url'      => !empty($returnUrlCustom) ? $returnUrlCustom : null,
            'webhook_url'     => !empty($webhookUrlCustom) ? $webhookUrlCustom : null,
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
        $gatewayReady = true;
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
            $gw = new MidtransGateway(GatewayConfig::getByCode('midtrans', $mode));
            if ($gw->isConfigured()) {
                $gatewayResponse = $gw->createPayment($params);
                if (!empty($gatewayResponse['snap_token'])) {
                    Transaction::update($trxId, ['snap_token' => $gatewayResponse['snap_token']]);
                }
            } else {
                $gatewayReady = false;
            }
        } elseif ($selectedGateway === 'duitku') {
            $gw = new DuitkuGateway(GatewayConfig::getByCode('duitku', $mode));
            if ($gw->isConfigured()) {
                $gatewayResponse = $gw->createPayment($params);
                if (!empty($gatewayResponse['reference'])) {
                    Transaction::update($trxId, ['gateway_reference' => $gatewayResponse['reference']]);
                }
            } else {
                $gatewayReady = false;
            }
        } elseif ($selectedGateway === 'doku') {
            $gw = new DokuGateway(GatewayConfig::getByCode('doku', $mode));
            if ($gw->isConfigured()) {
                $gatewayResponse = $gw->createPayment($params);
            } else {
                $gatewayReady = false;
            }
        }

        // Kredensial untuk mode yang diminta belum diisi
        if (!$gatewayReady) {
            $errMsg = 'Gateway ' . strtoupper($selectedGateway) . ' belum dikonfigurasi untuk mode ' . $mode . '. Lengkapi kredensial ' . ($mode === 'live' ? 'Live (Production)' : 'Sandbox') . ' di menu Setup Gateway.';
            Log::error('api', $errMsg, ['gateway' => $selectedGateway, 'mode' => $mode, 'trx_reference' => $trxRef]);
            Transaction::update($trxId, ['status' => 'failed']);
            $this->json(['status' => 'error', 'message' => $errMsg], 422);
            return;
        }

        // If the gateway explicitly failed (e.g. gateway server down, bad credentials)
        if ($gatewayResponse !== null && empty($gatewayResponse['success'])) {
            $errMsg = $gatewayResponse['message'] ?? 'Gagal membuat tagihan di payment gateway (' . strtoupper($selectedGateway) . ').';
            Log::error('api', "Payment gateway creation failed: {$errMsg}", ['gateway' => $selectedGateway, 'trx_reference' => $trxRef]);
            Transaction::update($trxId, ['status' => 'failed']);
            $this->json([
                'status' => 'error',
                'message' => $errMsg
            ], 422);
            return;
        }

        $paymentUrl = View::url('pay?ref=' . urlencode($trxRef));

        Log::info('api', "Payment created for app {$app['app_code']}, Order: {$orderId}, Ref: {$trxRef}, Mode: {$mode}, Gateway: {$selectedGateway}");

        $this->json([
            'status' => 'success',
            'message' => 'Transaksi pembayaran berhasil dibuat.',
            'data' => [
                'trx_reference'       => $trxRef,
                'order_id'            => $orderId,
                'amount'              => $amount,
                'gateway'             => $selectedGateway,
                'mode'                => $mode,
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

        $statusMode = GatewayConfig::normalizeMode($request->input('mode', ''), '');
        $trx = Transaction::findByOrderIdAndApp($orderId, $app['id'], $statusMode ?: null);
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
                'mode'             => $trx['mode'] ?? 'sandbox',
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
                'live_ready'    => !empty(GatewayConfig::getByCode($g['gateway_code'], 'live')['server_key']),
                'sandbox_ready' => !empty(GatewayConfig::getByCode($g['gateway_code'], 'sandbox')['server_key']),
            ];
        }, $gateways);

        $this->json([
            'status' => 'success',
            'data' => $list
        ]);
    }
}

