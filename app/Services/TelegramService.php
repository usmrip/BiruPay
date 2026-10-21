<?php
namespace App\Services;

use App\Models\Setting;
use App\Models\Log;

class TelegramService {
    public static function isEnabled(): bool {
        return (bool) Setting::get('telegram_enabled', '0');
    }

    public static function sendNotification(array $transaction, array $application): bool {
        if (!self::isEnabled()) {
            return false;
        }

        $botToken = Setting::get('telegram_bot_token');
        $chatId   = Setting::get('telegram_chat_id');

        if (empty($botToken) || empty($chatId)) {
            Log::warning('telegram', 'Telegram not configured: bot token or chat ID missing');
            return false;
        }

        $amountFormatted = 'Rp ' . number_format((float)$transaction['amount'], 0, ',', '.');
        $dateFormatted   = date('d M Y - H:i', strtotime($transaction['paid_at'] ?? $transaction['created_at'])) . ' WIB';
        $gatewayName     = strtoupper($transaction['gateway']);
        $paymentMethod   = !empty($transaction['payment_method']) ? ' (' . strtoupper($transaction['payment_method']) . ')' : '';

        $message = "🎉 <b>PEMBAYARAN BERHASIL DITERIMA!</b>\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "🏢 <b>Platform:</b> " . htmlspecialchars($application['app_name'] ?? 'Biru App') . "\n";
        $message .= "🧾 <b>Order ID:</b> <code>" . htmlspecialchars($transaction['order_id']) . "</code>\n";
        $message .= "🔖 <b>Ref Hub:</b> <code>" . htmlspecialchars($transaction['trx_reference']) . "</code>\n";
        $message .= "💳 <b>Gateway:</b> {$gatewayName}{$paymentMethod}\n";
        $message .= "💰 <b>Nominal:</b> <b>{$amountFormatted}</b>\n";
        $message .= "👤 <b>Pelanggan:</b> " . htmlspecialchars($transaction['customer_name'] ?? 'Pelanggan') . "\n";
        if (!empty($transaction['customer_email'])) {
            $message .= "📧 <b>Email:</b> " . htmlspecialchars($transaction['customer_email']) . "\n";
        }
        $message .= "📅 <b>Waktu:</b> {$dateFormatted}\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "✅ Status: <b>LUNAS / SETTLED</b>\n";
        $message .= "🌐 <i>Sistem Pembayaran Terpusat Biru Indonesia Creative</i>";

        return self::sendMessage($botToken, $chatId, $message);
    }

    public static function sendTest(string $botToken, string $chatId): array {
        $text = "🔔 <b>TES KONEKSI TELEGRAM BERHASIL!</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "Sistem Pembayaran Terpusat <b>Biru Indonesia Creative</b> berhasil terhubung dengan bot Telegram Anda.\n";
        $text .= "📅 Waktu: " . date('d-m-Y H:i:s') . " WIB\n";
        $text .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $text .= "✅ Konfigurasi notifikasi Telegram telah aktif dan siap menerima laporan transaksi real-time.";

        $success = self::sendMessage($botToken, $chatId, $text);
        if ($success) {
            return ['success' => true, 'message' => 'Pesan tes berhasil dikirim ke Telegram!'];
        }

        return ['success' => false, 'message' => 'Gagal mengirim pesan ke Telegram. Periksa kembali Bot Token dan Chat ID Anda.'];
    }

    public static function sendMessage(string $token, string $chatId, string $htmlMessage): bool {
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id'    => $chatId,
            'text'       => $htmlMessage,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            Log::error('telegram', 'Failed to send Telegram notification', [
                'http_code' => $httpCode,
                'error'     => $curlError,
                'response'  => $response
            ]);
            return false;
        }

        Log::info('telegram', 'Telegram notification sent successfully', ['chat_id' => $chatId]);
        return true;
    }
}

