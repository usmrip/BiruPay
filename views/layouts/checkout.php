<?php
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= View::escape($pageTitle ?? 'Pembayaran') ?></title>
    <link rel="stylesheet" href="<?= View::asset('css/bootstrap-icons.min.css') ?>">
    <style>
        :root {
            --apple-bg: #f5f5f7;
            --apple-card: #ffffff;
            --apple-text: #1d1d1f;
            --apple-secondary: #86868b;
            --apple-border: rgba(0, 0, 0, 0.08);
            --apple-btn: #000000;
            --apple-btn-hover: #1d1d1f;
            --apple-green: #34c759;
            --apple-radius: 18px;
        }

        * {
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--apple-bg);
            color: var(--apple-text);
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .apple-container {
            width: 100%;
            max-width: 460px;
            padding: 24px 16px;
            margin: 0 auto;
        }

        .apple-card {
            background: var(--apple-card);
            border-radius: var(--apple-radius);
            border: 1px solid var(--apple-border);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .btn-apple {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 14px 20px;
            background-color: var(--apple-btn);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.15s ease, transform 0.1s ease;
            letter-spacing: -0.01em;
        }

        .btn-apple:hover {
            background-color: #333336;
            color: #ffffff;
        }

        .btn-apple:active {
            transform: scale(0.99);
        }

        .btn-apple-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px 20px;
            background-color: transparent;
            color: var(--apple-text);
            border: 1px solid var(--apple-border);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            margin-top: 10px;
        }

        .btn-apple-secondary:hover {
            background-color: rgba(0, 0, 0, 0.03);
            border-color: rgba(0, 0, 0, 0.15);
        }

        .pill-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.02em;
            background: rgba(0, 0, 0, 0.04);
            color: var(--apple-secondary);
        }

        .table-summary {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table-summary td {
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        .table-summary tr:last-child td {
            border-bottom: none;
        }

        .table-summary .label {
            color: var(--apple-secondary);
        }

        .table-summary .value {
            text-align: right;
            font-weight: 500;
            color: var(--apple-text);
        }
    </style>
</head>
<body>

<div class="apple-container">
    <?= $content ?>
</div>

<script src="<?= View::asset('js/jquery.min.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
