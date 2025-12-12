<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Configuration - Aventones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilos_configuration.css') ?>">
    <script src="<?= base_url('assets/js/configuration.js') ?>" defer></script>
</head>
<body>

<header class="main-header">
    <div class="logo-container">
        <img src="<?= base_url('assets/img/icono_carros.png') ?>" alt="Logo" class="logo">
        <h1>AVENTONES</h1>
    </div>

    <nav class="nav-bar">
        <?php if ((session('user_role') ?? '') === 'driver'): ?>
            <a href="<?= site_url('driver/my-rides') ?>">My Rides</a>
            <a href="<?= site_url('driver/rides/new-ride') ?>">New Ride</a>
            <a href="<?= site_url('driver/vehicles') ?>">Vehicles</a>
            <a href="<?= site_url('driver/bookings') ?>">Bookings</a>
        <?php else: ?>
            <a href="<?= site_url('passenger/search-rides') ?>">Search Rides</a>
            <a href="<?= site_url('passenger/bookings') ?>">My Bookings</a>
        <?php endif; ?>

        <div class="user-menu" style="margin-left:auto">
            <img src="<?= base_url('assets/img/user_icon.png') ?>" class="user-icon" alt="User Icon">
            <div class="user-dropdown">
                <a href="<?= site_url('logout') ?>">Logout</a>
                <a class="active" href="<?= site_url('profile/configuration') ?>">Settings</a>
                <a href="<?= site_url('profile/edit') ?>">Profile</a>
            </div>
        </div>
    </nav>
</header>

<div class="configuration-container">
    <h2>Configuration</h2>

    <form class="configuration-form" autocomplete="off">
        <label for="public-name">Public Name</label>
        <input type="text" id="public-name" name="public-name" value="" readonly>

        <label for="public-bio">Public Bio</label>
        <textarea id="public-bio" name="public-bio" rows="6"></textarea>

        <div class="button-group">
            <a href="javascript:history.back()" class="cancel-btn">Cancel</a>
            <button type="submit" class="save-btn">Save</button>
        </div>
    </form>
</div>

</body>
</html>
