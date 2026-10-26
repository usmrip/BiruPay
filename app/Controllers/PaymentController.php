<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Transaction;
use App\Models\Application;
use App\Models\GatewayConfig;
use App\Gateways\MidtransGateway;
use App\Gateways\DuitkuGateway;
use App\Gateways\DokuGateway;

class PaymentController extends Controller {
    public function checkout(Request $request): void {
        $ref = trim($request->get('ref', ''));
        if (empty($ref)) {
            Response::notFound('Referensi transaksi tidak ditemukan.');
            return;
        }

        $trx = Transaction::findByReference($ref);
        if (!$trx) {
            Response::notFound("Transaksi dengan referensi '{$ref}' tidak ditemukan.");
            return;
        }

        if ($trx['status'] === 'pending') {
            $trx = \App\Services\PaymentSyncService::syncTransaction($trx);
        }

        $app = Application::find($trx['app_id']);
        $activeGateways = GatewayConfig::getActiveGateways();

        // Konfigurasi frontend gateway sesuai gateway & mode (live/sandbox) transaksi
        $trxMode = $trx['mode'] ?? 'sandbox';
        $activeGateway = $trx['gateway'] ?? 'midtrans';
        $gatewayConfigs = [];
        $cfg = GatewayConfig::getByCode($activeGateway, $trxMode);
        if ($cfg) {
            $gatewayConfigs[$activeGateway] = $this->makeGateway($activeGateway, $cfg)->getFrontendConfig();
        }

        $this->render('payment.checkout', [
            'pageTitle' => 'Pembayaran - ' . ($app['app_name'] ?? 'Biru Indonesia Creative'),
            'trx' => $trx,
            'app' => $app,
            'gateways' => $activeGateways,
            'gatewayConfigs' => $gatewayConfigs,
        ], 'checkout');
    }

    private function makeGateway(string $code, ?array $config) {
        return match ($code) {
            'midtrans' => new MidtransGateway($config),
            'duitku'   => new DuitkuGateway($config),
            default    => new DokuGateway($config),
        };
    }

    /**
     * AJAX endpoint to initiate payment token on the checkout page if gateway is switched or newly chosen
     */
    public function initiateGateway(Request $request): void {
        $trxRef  = trim($request->input('ref', ''));
        $gateway = trim($request->input('gateway', ''));

        $trx = Transaction::findByReference($trxRef);
        if (!$trx) {
            $this->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
            return;
        }

        if ($trx['status'] === 'success') {
            $this->json(['success' => false, 'message' => 'Transaksi ini sudah lunas.'], 400);
            return;
        }

        $app = Application::find($trx['app_id']);

        $params = [
            'trx_reference'   => $trx['trx_reference'],
            'order_id'        => $trx['order_id'],
            'amount'          => (float) $trx['amount'],
            'customer_name'   => $trx['customer_name'],
            'customer_email'  => $trx['customer_email'],
            'customer_phone'  => $trx['customer_phone'],
            'app_id'          => $trx['app_id'],
            'app_code'        => $app['app_code'] ?? 'BIRU',
            'item_name'       => 'Order ' . $trx['order_id'],
            'item_details'    => json_decode($trx['item_details'] ?? '[]', true),
        ];

        $result = ['success' => false, 'message' => 'Gateway tidak didukung.'];
        $trxMode = $trx['mode'] ?? 'sandbox';

        if (in_array($gateway, ['midtrans', 'duitku', 'doku'], true)) {
            $gw = $this->makeGateway($gateway, GatewayConfig::getByCode($gateway, $trxMode));
            $result = $gw->createPayment($params);
            if (!empty($result['success'])) {
                $update = ['gateway' => $gateway];
                if ($gateway === 'midtrans') {
                    $update['snap_token'] = $result['snap_token'];
                } elseif ($gateway === 'duitku') {
                    $update['gateway_reference'] = $result['reference'];
                } else {
                    $update['gateway_reference'] = $result['trx_reference'];
                }
                Transaction::update($trx['id'], $update);
            }
        }

        $this->json($result);
    }

    public function paymentReturn(Request $request): void {
        $ref = trim($request->get('ref', '') ?: $request->get('order_id', '') ?: $request->get('merchantOrderId', ''));
        $trx = null;

        if (!empty($ref)) {
            $trx = Transaction::findByReference($ref) ?: Transaction::findByOrderId($ref);
            if ($trx && $trx['status'] === 'pending') {
                $trx = \App\Services\PaymentSyncService::syncTransaction($trx);
            }
        }

        $app = $trx ? Application::find($trx['app_id']) : null;

        $this->render('payment.success', [
            'pageTitle' => 'Status Pembayaran',
            'trx' => $trx,
            'app' => $app,
        ], 'checkout');
    }
}

