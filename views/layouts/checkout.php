<?php
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= View::escape($pageTitle ?? 'Pembayaran') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
