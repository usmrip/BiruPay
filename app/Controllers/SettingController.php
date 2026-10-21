<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Models\Setting;
use App\Services\TelegramService;
use App\Services\EmailService;

class SettingController extends Controller {
    public function index(Request $request): void {
        $settings = Setting::getAll();

        $this->render('settings.index', [
            'pageTitle' => 'Pengaturan Notifikasi & Sistem',
            'activeNav' => 'settings',
            'settings'  => $settings,
        ]);
    }

    public function update(Request $request): void {
        $this->validateCsrf($request);

        $keys = [
            'app_name',
            'company_name',
            'telegram_enabled',
            'telegram_bot_token',
            'telegram_chat_id',
            'email_enabled',
            'mail_driver',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
            'admin_notification_email'
        ];

        foreach ($keys as $key) {
            $value = $request->post($key, '');
            if ($key === 'telegram_enabled' || $key === 'email_enabled') {
                $value = $value ? '1' : '0';
            }
            Setting::set($key, trim($value));
        }

        Session::setFlash('success', 'Pengaturan sistem & notifikasi berhasil disimpan!');
        $this->redirect('settings');
    }

    public function testTelegram(Request $request): void {
        $this->validateCsrf($request);

        $botToken = trim($request->post('telegram_bot_token', ''));
        $chatId   = trim($request->post('telegram_chat_id', ''));

        if (empty($botToken) || empty($chatId)) {
            Session::setFlash('danger', 'Bot Token dan Chat ID wajib diisi untuk melakukan tes Telegram.');
            $this->redirect('settings');
            return;
        }

        $res = TelegramService::sendTest($botToken, $chatId);
        if ($res['success']) {
            Session::setFlash('success', $res['message']);
        } else {
            Session::setFlash('danger', $res['message']);
        }

        $this->redirect('settings');
    }

    public function testEmail(Request $request): void {
        $this->validateCsrf($request);

        $toEmail = trim($request->post('test_email', ''));
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('danger', 'Alamat email tujuan tes tidak valid.');
            $this->redirect('settings');
            return;
        }

        $res = EmailService::sendTest($toEmail);
        if ($res['success']) {
            Session::setFlash('success', $res['message']);
        } else {
            Session::setFlash('danger', $res['message']);
        }

        $this->redirect('settings');
    }
}

