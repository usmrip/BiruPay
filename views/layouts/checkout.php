<?php
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::escape($pageTitle ?? 'Checkout Pembayaran') ?></title>
    <link rel="stylesheet" href="<?= View::asset('css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= View::asset('css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= View::asset('css/app.css') ?>">
    <style>
        body {
            background-color: #f1f5f9;
        }
        .checkout-container {
            max-width: 580px;
            margin: 30px auto;
        }
        .checkout-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .gateway-selector-item {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .gateway-selector-item:hover {
            border-color: #93c5fd;
            background-color: #f8fafc;
        }
        .gateway-selector-item.selected {
            border-color: #0052cc;
            background-color: #eff6ff;
        }
    </style>
</head>
<body>

<div class="checkout-container px-3">
    <?= $content ?>
</div>

<script src="<?= View::asset('js/jquery.min.js') ?>"></script>
<script src="<?= View::asset('js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= View::asset('js/app.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>

