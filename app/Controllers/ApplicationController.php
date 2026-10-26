<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Application;
use App\Models\Transaction;
use App\Models\GatewayConfig;
use App\Models\Setting;

class ApplicationController extends Controller {
    public function index(Request $request): void {
        $applications = Application::all('id DESC');

        // Append stats to each application
        foreach ($applications as &$app) {
            $app['trx_count'] = Transaction::count(['app_id' => $app['id']]);
            $app['resolved_mode'] = Application::resolveMode($app);
            $app['live_gateway'] = Application::resolveGateway($app, 'live');
            $app['sandbox_gateway'] = Application::resolveGateway($app, 'sandbox');
            $app['effective_gateway'] = Application::resolveGateway($app, $app['resolved_mode']);
        }

        $activeGateways = GatewayConfig::getActiveGateways();
        $allGateways = GatewayConfig::all('id ASC');
        $globalDefaultGateway = Setting::get('default_gateway', 'duitku');

        $this->render('applications.index', [
            'pageTitle' => 'Platform & Aplikasi Biru',
            'activeNav' => 'applications',
            'applications' => $applications,
            'activeGateways' => $activeGateways,
            'allGateways' => $allGateways,
            'globalDefaultGateway' => $globalDefaultGateway,
        ]);
    }

    public function create(Request $request): void {
        $activeGateways = GatewayConfig::getActiveGateways();
        $globalDefaultGateway = Setting::get('default_gateway', 'duitku');

        $this->render('applications.form', [
            'pageTitle' => 'Tambah Platform / Aplikasi Baru',
            'activeNav' => 'applications',
            'application' => null,
            'activeGateways' => $activeGateways,
            'globalDefaultGateway' => $globalDefaultGateway,
            'defaultApiKey' => Application::generateApiKey(),
        ]);
    }

    public function store(Request $request): void {
        $this->validateCsrf($request);

        $appCode   = strtoupper(trim(preg_replace('/[^a-zA-Z0-9_-]/', '', $request->post('app_code', ''))));
        $appName   = trim($request->post('app_name', ''));
        $apiKey    = trim($request->post('api_key', '')) ?: Application::generateApiKey();
        $webhook   = trim($request->post('webhook_url', ''));
        $return    = trim($request->post('return_url', ''));
        $desc      = trim($request->post('description', ''));
        $timezone  = trim($request->post('timezone', 'UTC'));
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            $timezone = 'UTC';
        }
        $mode = trim($request->post('mode', 'sandbox')) === 'live' ? 'live' : 'sandbox';
        $gatewayLive = trim($request->post('gateway_live', 'default'));
        if (!in_array($gatewayLive, ['default', 'midtrans', 'duitku', 'doku'])) {
            $gatewayLive = 'default';
        }
        $gatewaySandbox = trim($request->post('gateway_sandbox', 'default'));
        if (!in_array($gatewaySandbox, ['default', 'midtrans', 'duitku', 'doku'])) {
            $gatewaySandbox = 'default';
        }
        $defaultGw = trim($request->post('default_gateway', 'default'));
        if (!in_array($defaultGw, ['default', 'midtrans', 'duitku', 'doku'])) {
            $defaultGw = 'default';
        }

        if (empty($appCode) || empty($appName)) {
            Session::setFlash('danger', 'Kode Platform dan Nama Platform wajib diisi.');
            $this->back();
            return;
        }

        if (Application::findByCode($appCode)) {
            Session::setFlash('danger', "Kode Platform '{$appCode}' sudah digunakan.");
            $this->back();
            return;
        }

        Application::create([
            'app_code'        => $appCode,
            'app_name'        => $appName,
            'api_key'         => $apiKey,
            'mode'            => $mode,
            'gateway_live'    => $gatewayLive,
            'gateway_sandbox' => $gatewaySandbox,
            'default_gateway' => $defaultGw,
            'webhook_url'     => $webhook,
            'return_url'      => $return,
            'timezone'        => $timezone,
            'description'     => $desc,
            'status'          => 'active'
        ]);

        Session::setFlash('success', "Platform '{$appName}' berhasil didaftarkan!");
        $this->redirect('applications');
    }

    public function edit(Request $request, $id): void {
        $app = Application::find($id);
        if (!$app) {
            Session::setFlash('danger', 'Platform tidak ditemukan.');
            $this->redirect('applications');
            return;
        }

        $activeGateways = GatewayConfig::getActiveGateways();
        $globalDefaultGateway = Setting::get('default_gateway', 'duitku');

        $this->render('applications.form', [
            'pageTitle' => 'Edit Platform: ' . $app['app_name'],
            'activeNav' => 'applications',
            'application' => $app,
            'activeGateways' => $activeGateways,
            'globalDefaultGateway' => $globalDefaultGateway,
        ]);
    }

    public function update(Request $request, $id): void {
        $this->validateCsrf($request);

        $app = Application::find($id);
        if (!$app) {
            Session::setFlash('danger', 'Platform tidak ditemukan.');
            $this->redirect('applications');
            return;
        }

        $appName   = trim($request->post('app_name', ''));
        $webhook   = trim($request->post('webhook_url', ''));
        $return    = trim($request->post('return_url', ''));
        $desc      = trim($request->post('description', ''));
        $timezone  = trim($request->post('timezone', 'UTC'));
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            $timezone = 'UTC';
        }
        $status    = $request->post('status') === 'inactive' ? 'inactive' : 'active';
        $mode      = trim($request->post('mode', 'sandbox')) === 'live' ? 'live' : 'sandbox';
        $gatewayLive = trim($request->post('gateway_live', 'default'));
        if (!in_array($gatewayLive, ['default', 'midtrans', 'duitku', 'doku'])) {
            $gatewayLive = 'default';
        }
        $gatewaySandbox = trim($request->post('gateway_sandbox', 'default'));
        if (!in_array($gatewaySandbox, ['default', 'midtrans', 'duitku', 'doku'])) {
            $gatewaySandbox = 'default';
        }
        $defaultGw = trim($request->post('default_gateway', 'default'));
        if (!in_array($defaultGw, ['default', 'midtrans', 'duitku', 'doku'])) {
            $defaultGw = 'default';
        }

        if (empty($appName)) {
            Session::setFlash('danger', 'Nama Platform wajib diisi.');
            $this->back();
            return;
        }

        Application::update($id, [
            'app_name'        => $appName,
            'mode'            => $mode,
            'gateway_live'    => $gatewayLive,
            'gateway_sandbox' => $gatewaySandbox,
            'default_gateway' => $defaultGw,
            'webhook_url'     => $webhook,
            'return_url'      => $return,
            'timezone'        => $timezone,
            'description'     => $desc,
            'status'          => $status
        ]);

        Session::setFlash('success', "Data platform '{$appName}' berhasil diperbarui!");
        $this->redirect('applications');
    }

    public function bulkGateway(Request $request): void {
        $this->validateCsrf($request);
        $targetGateway = trim($request->post('target_gateway', 'default'));

        if (!in_array($targetGateway, ['default', 'midtrans', 'duitku', 'doku'])) {
            Session::setFlash('danger', 'Payment gateway yang dipilih tidak valid.');
            $this->back();
            return;
        }

        $count = Application::updateBulkGateway($targetGateway);
        $gwLabel = match($targetGateway) {
            'midtrans' => 'Midtrans (Snap Popup)',
            'duitku'   => 'Duitku (Duitku Pop)',
            'doku'     => 'DOKU Checkout',
            default    => 'Default Sistem'
        };

        Session::setFlash('success', "Berhasil memperbarui {$count} platform untuk menggunakan {$gwLabel}!");
        $this->redirect('applications');
    }

    public function bulkMode(Request $request): void {
        $this->validateCsrf($request);
        $targetMode = trim($request->post('target_mode', 'sandbox')) === 'live' ? 'live' : 'sandbox';

        $count = Application::updateBulkMode($targetMode);
        $modeLabel = $targetMode === 'live' ? 'LIVE (Produksi)' : 'SANDBOX (Uji Coba)';

        Session::setFlash('success', "Berhasil mengubah {$count} platform ke mode {$modeLabel}!");
        $this->redirect('applications');
    }

    public function setGlobalDefaultGateway(Request $request): void {
        $this->validateCsrf($request);
        $globalGateway = trim($request->post('global_gateway', 'duitku'));
        if (in_array($globalGateway, ['midtrans', 'duitku', 'doku'])) {
            Setting::set('default_gateway', $globalGateway);
            Session::setFlash('success', "Default Payment Gateway Sistem berhasil diubah ke " . strtoupper($globalGateway) . "!");
        }
        $this->redirect('applications');
    }

    public function quickSetMode(Request $request, $id): void {
        $this->validateCsrf($request);
        $app = Application::find($id);
        if (!$app) {
            Session::setFlash('danger', 'Platform tidak ditemukan.');
            $this->redirect('applications');
            return;
        }
        $mode = trim($request->post('mode', 'sandbox')) === 'live' ? 'live' : 'sandbox';
        Application::update($id, ['mode' => $mode]);
        $modeBadge = $mode === 'live' ? 'LIVE (Produksi)' : 'SANDBOX (Uji Coba)';
        Session::setFlash('success', "Mode platform '{$app['app_name']}' berhasil diubah ke {$modeBadge}!");
        $this->redirect('applications');
    }

    public function quickSetGateway(Request $request, $id): void {
        $this->validateCsrf($request);
        $app = Application::find($id);
        if (!$app) {
            Session::setFlash('danger', 'Platform tidak ditemukan.');
            $this->redirect('applications');
            return;
        }
        $gw = trim($request->post('gateway', 'default'));
        $target = trim($request->post('target', 'all'));
        if (in_array($gw, ['default', 'midtrans', 'duitku', 'doku'])) {
            $data = [];
            $gwName = strtoupper($gw);
            if ($target === 'live') {
                $data['gateway_live'] = $gw;
                $label = "Gateway Live untuk '{$app['app_name']}' diubah ke {$gwName}!";
            } elseif ($target === 'sandbox') {
                $data['gateway_sandbox'] = $gw;
                $label = "Gateway Sandbox untuk '{$app['app_name']}' diubah ke {$gwName}!";
            } else {
                $data['default_gateway'] = $gw;
                $label = "Gateway platform '{$app['app_name']}' diubah ke {$gwName}!";
            }
            Application::update($id, $data);
            Session::setFlash('success', $label);
        }
        $this->redirect('applications');
    }

    public function regenerateKey(Request $request, $id): void {
        $this->validateCsrf($request);

        $app = Application::find($id);
        if (!$app) {
            Session::setFlash('danger', 'Platform tidak ditemukan.');
            $this->redirect('applications');
            return;
        }

        $newKey = Application::generateApiKey();
        Application::update($id, ['api_key' => $newKey]);

        Session::setFlash('success', "API Key untuk '{$app['app_name']}' berhasil diperbarui!");
        $this->redirect('applications');
    }

    public function delete(Request $request, $id): void {
        $this->validateCsrf($request);

        $app = Application::find($id);
        if ($app) {
            Application::delete($id);
            Session::setFlash('success', "Platform '{$app['app_name']}' berhasil dihapus.");
        }

        $this->redirect('applications');
    }
}

