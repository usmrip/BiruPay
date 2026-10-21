-- Default Seed Data for Biru Payment Hub
-- Default admin password: Password123!
-- Hash generated using password_hash('Password123!', PASSWORD_BCRYPT)

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `status`)
VALUES 
(1, 'Administrator Biru', 'admin', 'admin@biru.co.id', '$2y$10$XheYPr3zAi8B4BvhoPtEGuhtUS7gbbCnecfb6YAbPoVTG1pTLyG/u', 'admin', 'active')
ON DUPLICATE KEY UPDATE `password`=VALUES(`password`);

INSERT INTO `gateway_configs` (`gateway_code`, `gateway_name`, `is_active`, `is_production`, `merchant_id`, `client_key`, `server_key`, `additional_config`)
VALUES
('midtrans', 'Midtrans Payment Gateway', 0, 0, '', '', '', '{"snap_popup": true, "enable_3ds": true}'),
('duitku', 'Duitku Pop Gateway', 0, 0, '', '', '', '{"expiry_period": 1440, "pop_modal": true}'),
('doku', 'Doku Payment Gateway', 0, 0, '', '', '', '{"checkout_type": "popup"}')
ON DUPLICATE KEY UPDATE `gateway_code`=`gateway_code`;

INSERT INTO `applications` (`app_code`, `app_name`, `api_key`, `webhook_url`, `return_url`, `description`, `status`)
VALUES
('BIRU-WEB', 'Biru Utama Website', 'biru_live_8f7b2c9d1e4a6f0b3c5d7e9a2b4c6d8e', 'https://biru.co.id/api/payment-callback', 'https://biru.co.id/payment/finish', 'Platform Utama Biru Indonesia Creative', 'active')
ON DUPLICATE KEY UPDATE `app_code`=`app_code`;

INSERT INTO `settings` (`setting_key`, `setting_value`)
VALUES
('app_name', 'Biru PayHub'),
('company_name', 'Biru Indonesia Creative'),
('telegram_enabled', '0'),
('telegram_bot_token', ''),
('telegram_chat_id', ''),
('email_enabled', '0'),
('mail_driver', 'smtp'),
('mail_host', 'mail.biru.co.id'),
('mail_port', '587'),
('mail_username', ''),
('mail_password', ''),
('mail_encryption', 'tls'),
('mail_from_address', 'no-reply@biru.co.id'),
('mail_from_name', 'Biru Payment Hub'),
('admin_notification_email', 'finance@biru.co.id')
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;

