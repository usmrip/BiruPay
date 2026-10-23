<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Transaction;
use App\Models\Application;
use App\Models\Log;
use App\Gateways\MidtransGateway;
use App\Gateways\DuitkuGateway;
use App\Gateways\DokuGateway;
use App\Services\TelegramService;
use App\Services\EmailService;
use App\Services\WebhookDispatcherService;

class WebhookController extends Controller {
    public function midtrans(Request $request): void {
        if ($request->method() === 'GET') {
            $this->json([
                'status'  => 'active',
                'gateway' => 'midtrans',
                'message' => 'Biru PayHub Midtrans Webhook Endpoint is active. POST notifications are accepted.'
            ]);
            return;
        }

        Log::info('webhook_midtrans', 'Midtrans webhook received', ['raw' => $request->rawBody()]);

        $gateway = new MidtransGateway();
        $result = $gateway->verifyWebhook($request);

        if (!$result['is_valid']) {
            Log::warning('webhook_midtrans', 'Midtrans webhook validation failed: ' . $result['message']);
            $this->json(['status' => 'error', 'message' => $result['message']], 400);
            return;
        }

        $this->processWebhookResult($result, 'midtrans');
    }

    public function duitku(Request $request): void {
        if ($request->method() === 'GET') {
            $this->json([
                'status'  => 'active',
                'gateway' => 'duitku',
                'message' => 'Biru PayHub Duitku Webhook Endpoint is active. POST notifications are accepted.'
            ]);
            return;
        }

        Log::info('webhook_duitku', 'Duitku webhook received', ['params' => $request->all(), 'raw' => $request->rawBody()]);

        $gateway = new DuitkuGateway();
        $result = $gateway->verifyWebhook($request);

        if (!$result['is_valid']) {
            Log::warning('webhook_duitku', 'Duitku webhook validation failed: ' . $result['message']);
            $this->json(['status' => 'error', 'message' => $result['message']], 400);
            return;
        }

        $this->processWebhookResult($result, 'duitku');
    }

    public function doku(Request $request): void {
        if ($request->method() === 'GET') {
            $this->json([
                'status'  => 'active',
                'gateway' => 'doku',
                'message' => 'Biru PayHub DOKU Webhook Endpoint is active. POST notifications are accepted.'
            ]);
            return;
        }

        Log::info('webhook_doku', 'DOKU webhook received', ['raw' => $request->rawBody()]);

        $gateway = new DokuGateway();
        $result = $gateway->verifyWebhook($request);

        if (!$result['is_valid']) {
            Log::warning('webhook_doku', 'DOKU webhook validation failed: ' . $result['message']);
            $this->json(['status' => 'error', 'message' => $result['message']], 400);
            return;
        }

        $this->processWebhookResult($result, 'doku');
    }

    private function processWebhookResult(array $result, string $gatewayName): void {
        $trxRef = $result['trx_reference'];
        $trx = Transaction::findByReference($trxRef);

        if (!$trx) {
            Log::error('webhook', "Transaction not found for reference {$trxRef} from {$gatewayName}");
            $this->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            return;
        }

        $updateData = [
            'status'               => $result['status'],
            'payment_method'       => $result['payment_method'] ?? $trx['payment_method'],
            'gateway_reference'    => $result['gateway_reference'] ?? $trx['gateway_reference'],
            'webhook_response_raw' => $result['raw_payload'],
        ];

        $wasAlreadyPaid = ($trx['status'] === 'success');

        if ($result['status'] === 'success' && !$wasAlreadyPaid) {
            $updateData['paid_at'] = date('Y-m-d H:i:s');
        }

        Transaction::update($trx['id'], $updateData);

        // Fetch refreshed transaction data and application
        $updatedTrx = Transaction::find($trx['id']);
        $app = Application::find($trx['app_id']);

        // Trigger notifications & webhook dispatch if status is success and not previously processed
        if ($result['status'] === 'success' && !$wasAlreadyPaid) {
            if ($app) {
                // Send Telegram Notification
                TelegramService::sendNotification($updatedTrx, $app);

                // Send Email Notification
                EmailService::sendNotification($updatedTrx, $app);

                // Forward Webhook to Client Application
                WebhookDispatcherService::dispatchToApp((int)$trx['id']);
            }
        }

        Log::info('webhook', "Transaction {$trxRef} updated to status '{$result['status']}' via {$gatewayName}");
        $this->json(['status' => 'ok', 'message' => 'Webhook processed successfully']);
    }
}

