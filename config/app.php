<?php
/**
 * Application Configuration
 * Biru Payment Hub - Biru Indonesia Creative
 */

return [
    'app_name' => 'Biru PayHub',
    'app_env' => 'production',
    'app_debug' => false,
    'timezone' => 'UTC',
    
    // Base URL of the payment hub (without trailing slash)
    'base_url' => (function() {
        $isHttps = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1')) || 
                   (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                   (isset($_SERVER['HTTP_CF_VISITOR']) && str_contains($_SERVER['HTTP_CF_VISITOR'], 'https')) ||
                   (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        $host = $_SERVER['HTTP_HOST'] ?? 'biru.co.id';
        // Always force https for live domains
        $scheme = ($isHttps || !in_array($host, ['localhost', '127.0.0.1'])) ? 'https' : 'http';
        return $scheme . '://' . $host . '/pembayaran';
    })(),

    // Secret salt for internal hashes
    'app_key' => 'b1ru_pay_hub_s3cr3t_k3y_9847120398',
];

