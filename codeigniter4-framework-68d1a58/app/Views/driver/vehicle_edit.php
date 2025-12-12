<?php
// Espera: $vehicle, $msg, $error
// $vehicle = ['id','plate','color','make','model','year','seat_capacity','photo_path']
$photo = $vehicle['photo_path']
    ? base_url(ltrim($vehicle['photo_path'], '/'))
    : base_url('Img/car_placeholder.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Vehicle</title>

    <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>?v=4">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>?v=4">
    <link rel="stylesheet" href="<?= base_url('assets/css/driver_vehicles.css'); ?>?v=4">
</head>
<body>
<header class="main-header">
    <div class="logo-container">
        <img src="<?= base_url('Img/icono_carros.png'); ?>" class="logo" alt="Logo">
        <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
        <a href="<?= site_url('driver/my-rides'); ?>">My Rides</a>
        <a href="<?= site_url('driver/new-ride'); ?>">New Ride</a>
        <a href="<?= site_url('driver/vehicles'); ?>" class="active">Vehicles</a>
        <a href="<?= site_url('driver/bookings'); ?>">Bookings</a>
        <div class="user-menu" style="margin-left:auto">
            <img src="<?= base_url('Img/user_icon.png'); ?>" class="user-icon" alt="User">
            <div class="user-dropdown">
                <a href="<?= site_url('logout'); ?>">Logout</a>
                <a href="<<?= site_url('profile/edit') ?>">Profile</a>
                <a href="<?= site_url('profile/configuration') ?>">Settings</a>
            </div>
        </div>
    </nav>
</header>

<main class="wrap">
    <?php if (!empty($error)): ?>
        <div class="msg err"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($msg)): ?>
        <div class="msg ok"><?= esc($msg) ?></div>
    <?php endif; ?>

    <div class="card" style="max-width:760px;margin:0 auto;">
        <div class="section-head">
            <h2>Edit Vehicle · <?= esc($vehicle['plate']) ?></h2>
            <img class="car-thumb" src="<?= esc($photo) ?>" alt="car" style="border-radius:8px;">
        </div>

        <form action="<?= site_url('driver/vehicles/update/' . (int)$vehicle['id']); ?>"
              method="post"
              enctype="multipart/form-data"
              class="grid2">

            <?= csrf_field() ?>

            <div>
                <label>Plate</label>
                <input type="text" name="plate" required
                       value="<?= esc($vehicle['plate']) ?>">
            </div>

            <div>
                <label>Color</label>
                <input type="text" name="color"
                       value="<?= esc($vehicle['color']) ?>">
            </div>

            <div>
                <label>Make</label>
                <input type="text" name="make" required
                       value="<?= esc($vehicle['make']) ?>">
            </div>

            <div>
                <label>Model</label>
                <input type="text" name="model" required
                       value="<?= esc($vehicle['model']) ?>">
            </div>

            <div>
                <label>Year</label>
                <input type="number" name="year" min="1950" max="2100" required
                       value="<?= (int)$vehicle['year'] ?>">
            </div>

            <div>
                <label>Seats</label>
                <input type="number" name="seat_capacity" min="1" max="9" required
                       value="<?= (int)$vehicle['seat_capacity'] ?>">
            </div>

            <div style="grid-column:1 / -1">
                <label>Replace Photo (optional)</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
            </div>

            <div style="grid-column:1 / -1;text-align:right">
                <a class="btn" href="<?= site_url('driver/vehicles'); ?>">Cancel</a>
                <button class="btn primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>

