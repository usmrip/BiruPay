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
    <link rel="stylesheet" href="<?= View::asset('css/checkout.css') ?>?v=<?= @filemtime(__DIR__ . '/../../public/assets/css/checkout.css') ?: 1 ?>">
</head>
<body>

<div class="apple-container">
    <?= $content ?>
</div>

<script src="<?= View::asset('js/jquery.min.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
