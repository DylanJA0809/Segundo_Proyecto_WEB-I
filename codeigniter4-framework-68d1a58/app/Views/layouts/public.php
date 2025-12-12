<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Aventones') ?></title>

    <!-- CSS generales -->
    <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilos_searchrides.css') ?>">

    <!-- JS específico de búsqueda pública -->
    <script src="<?= base_url('assets/js/search_public_rides.js') ?>" defer></script>
</head>
<body>
    <?= $this->renderSection('content') ?>
</body>
</html>
