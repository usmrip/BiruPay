<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Transaction;
use App\Models\Application;
use App\Services\TelegramService;
use App\Services\EmailService;
use App\Services\WebhookDispatcherService;

class TransactionController extends Controller {
    public function index(Request $request): void {
        $page = max(1, (int) $request->get('page', 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'app_id'     => $request->get('app_id', ''),
            'gateway'    => $request->get('gateway', ''),
            'status'     => $request->get('status', ''),
            'start_date' => $request->get('start_date', ''),
            'end_date'   => $request->get('end_date', ''),
            'search'     => $request->get('search', ''),
        ];

        $totalTransactions = Transaction::countFiltered($filters);
        $totalPages = ceil($totalTransactions / $limit);
        $transactions = Transaction::filter($filters, 't.id DESC', $limit, $offset);
        $applications = Application::all('app_name ASC');

        $this->render('transactions.index', [
            'pageTitle'         => 'Laporan Transaksi',
            'activeNav'         => 'transactions',
            'transactions'      => $transactions,
            'applications'      => $applications,
            'filters'           => $filters,
            'currentPage'       => $page,
            'totalPages'        => $totalPages,
            'totalTransactions' => $totalTransactions,
        ]);
    }

    public function show(Request $request, $id): void {
        $trx = Transaction::getWithApplication((int) $id);
        if (!$trx) {
            Session::setFlash('danger', 'Transaksi tidak ditemukan.');
            $this->redirect('transactions');
            return;
        }

        $this->render('transactions.show', [
            'pageTitle' => 'Detail Transaksi #' . $trx['order_id'],
            'activeNav' => 'transactions',
            'trx' => $trx,
        ]);
    }

    public function resendWebhook(Request $request, $id): void {
        $this->validateCsrf($request);

        $trx = Transaction::find($id);
        if (!$trx) {
            Session::setFlash('danger', 'Transaksi tidak ditemukan.');
            $this->back();
            return;
        }

        $success = WebhookDispatcherService::dispatchToApp((int)$id);
        if ($success) {
            Session::setFlash('success', 'Notifikasi webhook berhasil dikirim ulang ke aplikasi terkait.');
        } else {
            Session::setFlash('warning', 'Pengiriman webhook gagal. Silakan periksa URL webhook aplikasi.');
        }

        $this->back();
    }

    public function markAsPaid(Request $request, $id): void {
        $this->validateCsrf($request);

        $trx = Transaction::find($id);
        if (!$trx) {
            Session::setFlash('danger', 'Transaksi tidak ditemukan.');
            $this->back();
            return;
        }

        $now = date('Y-m-d H:i:s');
        Transaction::update($id, [
            'status' => 'success',
            'paid_at' => $now,
            'payment_method' => $trx['payment_method'] ?: 'manual_settlement'
        ]);

        $app = Application::find($trx['app_id']);
        $updatedTrx = Transaction::find($id);

        if ($app) {
            TelegramService::sendNotification($updatedTrx, $app);
            EmailService::sendNotification($updatedTrx, $app);
            WebhookDispatcherService::dispatchToApp((int)$id);
        }

        Session::setFlash('success', 'Transaksi berhasil ditandai Lunas dan notifikasi telah dikirimkan.');
        $this->back();
    }

    public function exportCsv(Request $request): void {
        $filters = [
            'app_id'     => $request->get('app_id', ''),
            'gateway'    => $request->get('gateway', ''),
            'status'     => $request->get('status', ''),
            'start_date' => $request->get('start_date', ''),
            'end_date'   => $request->get('end_date', ''),
            'search'     => $request->get('search', ''),
        ];

        $transactions = Transaction::filter($filters, 't.id DESC', 5000, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Laporan_Transaksi_BiruPay_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'ID', 'Platform', 'Order ID', 'Ref Hub', 'Gateway', 'Payment Method', 
            'Nominal (Rp)', 'Status', 'Pelanggan', 'Email', 'No HP', 'Notifikasi App', 'Waktu Dibuat', 'Waktu Bayar'
        ]);

        foreach ($transactions as $t) {
            fputcsv($out, [
                $t['id'],
                $t['app_name'] ?? '-',
                $t['order_id'],
                $t['trx_reference'],
                strtoupper($t['gateway']),
                $t['payment_method'] ?? '-',
                $t['amount'],
                strtoupper($t['status']),
                $t['customer_name'] ?? '-',
                $t['customer_email'] ?? '-',
                $t['customer_phone'] ?? '-',
                $t['app_notify_status'] ?? '-',
                $t['created_at'],
                $t['paid_at'] ?? '-'
            ]);
        }

        fclose($out);
        exit;
    }
}

