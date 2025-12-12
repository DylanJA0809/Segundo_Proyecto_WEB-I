<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Aventones - Login') ?></title>

    <!-- CSS del login -->
    <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css?v=2') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilo_Login_Register.css?v=2') ?>">

    <!-- JS-->
    <script src="<?= base_url('assets/js/login.js') ?>" defer></script>
    <script src="<?= base_url('assets/js/user_registration.js') ?>" defer></script>

</head>
<body>
    <?= $this->renderSection('content') ?>
</body>
</html>
