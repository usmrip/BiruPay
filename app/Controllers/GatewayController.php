<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\GatewayConfig;

class GatewayController extends Controller {
    public function index(Request $request): void {
        $midtrans = GatewayConfig::getByCode('midtrans');
        $duitku   = GatewayConfig::getByCode('duitku');
        $doku     = GatewayConfig::getByCode('doku');
        $globalDefaultGateway = \App\Models\Setting::get('default_gateway', 'duitku');
        $totalApps = \App\Models\Application::count();

        $this->render('gateways.index', [
            'pageTitle' => 'Setup Payment Gateways (Sandbox & Live)',
            'activeNav' => 'gateways',
            'midtrans'  => $midtrans,
            'duitku'    => $duitku,
            'doku'      => $doku,
            'globalDefaultGateway' => $globalDefaultGateway,
            'totalApps' => $totalApps,
        ]);
    }

    public function update(Request $request, string $gatewayCode): void {
        $this->validateCsrf($request);

        $gateway = GatewayConfig::getByCode($gatewayCode);
        if (!$gateway) {
            Session::setFlash('danger', 'Gateway tidak dikenali.');
            $this->redirect('gateways');
            return;
        }

        $isActive = $request->post('is_active') ? 1 : 0;
        $mode = $request->post('mode', 'sandbox');
        $isProduction = ($mode === 'production' || $request->post('is_production')) ? 1 : 0;

        // Sandbox keys
        $sandboxMerchantId = trim($request->post('sandbox_merchant_id', ''));
        $sandboxClientKey  = trim($request->post('sandbox_client_key', ''));
        $sandboxServerKey  = trim($request->post('sandbox_server_key', ''));

        // Production keys
        $prodMerchantId = trim($request->post('production_merchant_id', ''));
        $prodClientKey  = trim($request->post('production_client_key', ''));
        $prodServerKey  = trim($request->post('production_server_key', ''));

        // Legacy / fallback sync
        $activeMerchantId = $isProduction ? $prodMerchantId : $sandboxMerchantId;
        $activeClientKey  = $isProduction ? $prodClientKey : $sandboxClientKey;
        $activeServerKey  = $isProduction ? $prodServerKey : $sandboxServerKey;

        $additional = $gateway['additional'] ?? [];

        if ($gatewayCode === 'duitku') {
            $additional['expiry_period'] = (int) $request->post('expiry_period', 1440);
        }

        $updateData = [
            'is_active'               => $isActive,
            'is_production'           => $isProduction,
            'sandbox_merchant_id'     => $sandboxMerchantId,
            'sandbox_client_key'      => $sandboxClientKey,
            'sandbox_server_key'      => $sandboxServerKey,
            'production_merchant_id'  => $prodMerchantId,
            'production_client_key'   => $prodClientKey,
            'production_server_key'   => $prodServerKey,
            // also maintain active keys in default columns
            'merchant_id'             => $activeMerchantId,
            'client_key'              => $activeClientKey,
            'server_key'              => $activeServerKey,
            'additional_config'       => json_encode($additional),
        ];

        GatewayConfig::update($gateway['id'], $updateData);

        $modeLabel = $isProduction ? 'Live (Production)' : 'Sandbox (Testing)';
        Session::setFlash('success', 'Konfigurasi ' . htmlspecialchars($gateway['gateway_name']) . ' berhasil disimpan! Mode aktif: ' . $modeLabel);
        $this->redirect('gateways');
    }
}
