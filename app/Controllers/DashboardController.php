<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Transaction;
use App\Models\Application;
use App\Models\GatewayConfig;

class DashboardController extends Controller {
    public function index(Request $request): void {
        $stats = Transaction::getDashboardStats();
        $recentTransactions = Transaction::filter([], 't.id DESC', 10);
        $applications = Application::all('app_name ASC');
        $gateways = GatewayConfig::all('id ASC');

        $this->render('dashboard.index', [
            'pageTitle' => 'Dashboard Overview',
            'activeNav' => 'dashboard',
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'applications' => $applications,
            'gateways' => $gateways,
        ]);
    }
}

