<?php
namespace App\Services;

use App\Models\Setting;
use App\Models\Log;

class EmailService {
    public static function isEnabled(): bool {
        return (bool) Setting::get('email_enabled', '0');
    }

    public static function sendNotification(array $transaction, array $application): bool {
        if (!self::isEnabled()) {
            return false;
        }

        $adminEmail = Setting::get('admin_notification_email');
        $customerEmail = $transaction['customer_email'] ?? '';
        $subject = 'Bukti Pembayaran Berhasil #' . $transaction['order_id'] . ' - ' . ($application['app_name'] ?? 'Biru');

        $htmlBody = self::buildReceiptHtml($transaction, $application);

        $sentAdmin = false;
        $sentCustomer = false;

        if (!empty($adminEmail)) {
            $sentAdmin = self::sendMail($adminEmail, '[Admin Alert] ' . $subject, $htmlBody);
        }

        if (!empty($customerEmail) && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $sentCustomer = self::sendMail($customerEmail, $subject, $htmlBody);
        }

        return $sentAdmin || $sentCustomer;
    }

    public static function sendTest(string $toEmail): array {
        $subject = 'Tes Notifikasi Email - Biru Payment Hub';
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
            <div style='background: #0052cc; padding: 20px; text-align: center; border-radius: 6px 6px 0 0;'>
                <h2 style='color: #ffffff; margin: 0;'>Biru Payment Hub</h2>
            </div>
            <div style='padding: 25px; background: #ffffff;'>
                <h3 style='color: #1e293b;'>Tes Konfigurasi Email Berhasil!</h3>
                <p>Halo,</p>
                <p>Email ini dikirimkan untuk memastikan bahwa konfigurasi email/SMTP pada Sistem Pembayaran Terpusat Biru Indonesia Creative telah berfungsi dengan baik.</p>
                <p><strong>Waktu Pengiriman:</strong> " . date('d-m-Y H:i:s') . " WIB</p>
            </div>
            <div style='background: #f8fafc; padding: 15px; text-align: center; font-size: 12px; color: #64748b;'>
                &copy; " . date('Y') . " Biru Indonesia Creative. All rights reserved.
            </div>
        </div>";

        $success = self::sendMail($toEmail, $subject, $body);
        if ($success) {
            return ['success' => true, 'message' => "Email tes berhasil dikirim ke $toEmail!"];
        }

        return ['success' => false, 'message' => "Gagal mengirim email ke $toEmail. Periksa pengaturan SMTP dan log server."];
    }

    public static function sendMail(string $to, string $subject, string $htmlBody): bool {
        $driver    = Setting::get('mail_driver', 'smtp');
        $fromEmail = Setting::get('mail_from_address', 'no-reply@biru.co.id');
        $fromName  = Setting::get('mail_from_name', 'Biru Payment Hub');

        if ($driver === 'smtp') {
            $host       = Setting::get('mail_host', 'localhost');
            $port       = (int) Setting::get('mail_port', 587);
            $username   = Setting::get('mail_username', '');
            $password   = Setting::get('mail_password', '');
            $encryption = Setting::get('mail_encryption', 'tls');

            if (!empty($host) && !empty($username)) {
                $smtpSuccess = self::sendSmtp($host, $port, $username, $password, $encryption, $fromEmail, $fromName, $to, $subject, $htmlBody);
                if ($smtpSuccess) {
                    Log::info('email', 'Email sent via SMTP to ' . $to);
                    return true;
                }
            }
        }

        // Fallback to PHP native mail()
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $mailSent = @mail($to, $subject, $htmlBody, $headers);
        if ($mailSent) {
            Log::info('email', 'Email sent via mail() to ' . $to);
            return true;
        }

        Log::error('email', 'Failed to send email to ' . $to);
        return false;
    }

    private static function sendSmtp(string $host, int $port, string $user, string $pass, string $encryption, string $from, string $fromName, string $to, string $subject, string $body): bool {
        $timeout = 15;
        $prefix = ($encryption === 'ssl') ? 'ssl://' : '';
        $socket = @fsockopen($prefix . $host, $port, $errno, $errstr, $timeout);

        if (!$socket) {
            Log::error('email', "SMTP Socket error: $errstr ($errno)");
            return false;
        }

        $read = function() use ($socket) {
            $data = '';
            while ($str = fgets($socket, 515)) {
                $data .= $str;
                if (substr($str, 3, 1) === ' ') break;
            }
            return $data;
        };

        $write = function(string $cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $read(); // Initial greeting

        $write("EHLO " . gethostname());
        $read();

        if ($encryption === 'tls') {
            $write("STARTTLS");
            $response = $read();
            if (substr($response, 0, 3) !== '220') {
                fclose($socket);
                return false;
            }
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $write("EHLO " . gethostname());
            $read();
        }

        if (!empty($user)) {
            $write("AUTH LOGIN");
            $read();
            $write(base64_encode($user));
            $read();
            $write(base64_encode($pass));
            $authRes = $read();
            if (substr($authRes, 0, 3) !== '235') {
                fclose($socket);
                Log::error('email', 'SMTP Auth failed: ' . $authRes);
                return false;
            }
        }

        $write("MAIL FROM: <{$from}>");
        $read();
        $write("RCPT TO: <{$to}>");
        $read();
        $write("DATA");
        $read();

        $headers  = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$from}>\r\n";
        $headers .= "To: <{$to}>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Date: " . date('r') . "\r\n";

        $write($headers . "\r\n" . $body . "\r\n.");
        $res = $read();

        $write("QUIT");
        fclose($socket);

        return substr($res, 0, 3) === '250';
    }

    private static function buildReceiptHtml(array $transaction, array $application): string {
        $amountFormatted = 'Rp ' . number_format((float)$transaction['amount'], 0, ',', '.');
        $dateFormatted   = date('d F Y, H:i', strtotime($transaction['paid_at'] ?? $transaction['created_at'])) . ' WIB';
        $appName         = htmlspecialchars($application['app_name'] ?? 'Biru Platform');
        $gateway         = strtoupper($transaction['gateway']);
        $method          = !empty($transaction['payment_method']) ? ' (' . strtoupper($transaction['payment_method']) . ')' : '';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Bukti Pembayaran</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
            <table role='presentation' border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #f1f5f9; padding: 40px 10px;'>
                <tr>
                    <td align='center'>
                        <table border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);'>
                            <!-- Header -->
                            <tr>
                                <td style='background-color: #0052cc; padding: 30px; text-align: center; color: #ffffff;'>
                                    <h1 style='margin: 0 0 10px 0; font-size: 24px; font-weight: 700;'>Biru Indonesia Creative</h1>
                                    <p style='margin: 0; opacity: 0.9; font-size: 14px;'>Sistem Pembayaran Terpusat</p>
                                </td>
                            </tr>
                            <!-- Status Banner -->
                            <tr>
                                <td style='padding: 30px 40px 20px 40px; text-align: center;'>
                                    <div style='display: inline-block; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 30px; padding: 6px 16px; margin-bottom: 15px;'>
                                        <span style='color: #059669; font-weight: 600; font-size: 14px;'>✓ Pembayaran Berhasil Diterima</span>
                                    </div>
                                    <h2 style='margin: 0; font-size: 32px; color: #0f172a; font-weight: 800;'>{$amountFormatted}</h2>
                                    <p style='margin: 5px 0 0 0; color: #64748b; font-size: 14px;'>Status: <strong>LUNAS</strong></p>
                                </td>
                            </tr>
                            <!-- Details Table -->
                            <tr>
                                <td style='padding: 10px 40px 30px 40px;'>
                                    <table border='0' cellpadding='0' cellspacing='0' width='100%' style='border-collapse: collapse; font-size: 14px; color: #334155;'>
                                        <tr style='border-bottom: 1px solid #e2e8f0;'>
                                            <td style='padding: 12px 0; color: #64748b;'>Platform / Layanan</td>
                                            <td style='padding: 12px 0; text-align: right; font-weight: 600;'>{$appName}</td>
                                        </tr>
                                        <tr style='border-bottom: 1px solid #e2e8f0;'>
                                            <td style='padding: 12px 0; color: #64748b;'>Order ID Platform</td>
                                            <td style='padding: 12px 0; text-align: right; font-family: monospace; font-size: 13px; font-weight: 600;'>{$transaction['order_id']}</td>
                                        </tr>
                                        <tr style='border-bottom: 1px solid #e2e8f0;'>
                                            <td style='padding: 12px 0; color: #64748b;'>Nomor Referensi Hub</td>
                                            <td style='padding: 12px 0; text-align: right; font-family: monospace; font-size: 13px; font-weight: 600;'>{$transaction['trx_reference']}</td>
                                        </tr>
                                        <tr style='border-bottom: 1px solid #e2e8f0;'>
                                            <td style='padding: 12px 0; color: #64748b;'>Payment Gateway</td>
                                            <td style='padding: 12px 0; text-align: right; font-weight: 600;'>{$gateway}{$method}</td>
                                        </tr>
                                        <tr style='border-bottom: 1px solid #e2e8f0;'>
                                            <td style='padding: 12px 0; color: #64748b;'>Nama Pelanggan</td>
                                            <td style='padding: 12px 0; text-align: right; font-weight: 600;'>" . htmlspecialchars($transaction['customer_name'] ?? 'Pelanggan') . "</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 12px 0; color: #64748b;'>Waktu Pembayaran</td>
                                            <td style='padding: 12px 0; text-align: right; font-weight: 600;'>{$dateFormatted}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;'>
                                    <p style='margin: 0;'>Terima kasih telah melakukan pembayaran melalui Biru Payment Hub.</p>
                                    <p style='margin: 5px 0 0 0;'>&copy; " . date('Y') . " Biru Indonesia Creative. All rights reserved.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }
}

