<?php
// Espera: $vehicles, $msg, $error
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aventones · Vehicles</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>?v=4">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>?v=4">
    <link rel="stylesheet" href="<?= base_url('assets/css/driver_vehicles.css'); ?>?v=4">
</head>
<body>
<header class="main-header">
    <div class="logo-container">
        <img src="<?= base_url('assets/img/icono_carros.png'); ?>" class="logo" alt="Logo">
        <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
        <a href="<?= site_url('driver/my-rides'); ?>">My Rides</a>
        <a href="<?= site_url('driver/new-ride'); ?>">New Ride</a>
        <a href="<?= site_url('driver/vehicles'); ?>" class="active">Vehicles</a>
        <a href="<?= site_url('driver/bookings'); ?>">Bookings</a>
        <div class="user-menu" style="margin-left:auto">
            <img src="<?= base_url('assets/img/user_icon.png'); ?>" class="user-icon" alt="User">
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

    <div class="row">
        <!-- FORMULARIO: ADD VEHICLE -->
        <div class="card">
            <div class="section-head"><h2>Add Vehicle</h2></div>

            <form action="<?= site_url('driver/vehicles/store'); ?>"
                  method="post"
                  enctype="multipart/form-data"
                  class="grid2"
                  autocomplete="off">

                <?= csrf_field() ?>

                <div>
                    <label>Plate</label>
                    <input type="text" name="plate" required placeholder="ABC-123"
                           value="<?= esc(old('plate')) ?>">
                </div>

                <div>
                    <label>Color</label>
                    <input type="text" name="color" placeholder="Blue"
                           value="<?= esc(old('color')) ?>">
                </div>

                <div>
                    <label>Make</label>
                    <input type="text" name="make" required placeholder="Toyota"
                           value="<?= esc(old('make')) ?>">
                </div>

                <div>
                    <label>Model</label>
                    <input type="text" name="model" required placeholder="Corolla"
                           value="<?= esc(old('model')) ?>">
                </div>

                <div>
                    <label>Year</label>
                    <input type="number" name="year" min="1950" max="2100" required placeholder="2018"
                           value="<?= esc(old('year')) ?>">
                </div>

                <div>
                    <label>Seats</label>
                    <input type="number" name="seat_capacity" min="1" max="9" required placeholder="4"
                           value="<?= esc(old('seat_capacity')) ?>">
                </div>

                <div style="grid-column:1 / -1">
                    <label>Photo (optional)</label>
                    <div class="file-upload">
                        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                        <label for="photo" class="file-label">Choose Photo</label>
                    </div>
                </div>

                <div style="grid-column:1 / -1;text-align:right">
                    <button class="btn primary" type="submit">Save Vehicle</button>
                </div>
            </form>
        </div>

        <!-- LISTA: MY VEHICLES -->
        <div class="card">
            <div class="section-head"><h2>My Vehicles</h2></div>

            <?php if (empty($vehicles)): ?>
                <p class="muted">No vehicles yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Plate</th>
                        <th>Make/Model</th>
                        <th>Year</th>
                        <th>Color</th>
                        <th>Seats</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($vehicles as $v): ?>
                        <?php
                        $photo = $v['photo_path']
                            ? base_url(ltrim($v['photo_path'], '/'))
                            : base_url('assets/img/car_placeholder.png');
                        ?>
                        <tr>
                            <td style="width:70px">
                                <img src="<?= esc($photo) ?>" alt="car" class="car-thumb">
                            </td>
                            <td><?= esc($v['plate']) ?></td>
                            <td><?= esc($v['make'] . ' ' . $v['model']) ?></td>
                            <td><?= (int)$v['year'] ?></td>
                            <td><?= esc($v['color'] ?: '—') ?></td>
                            <td><?= (int)$v['seat_capacity'] ?></td>
                            <td class="actions">
                                <a class="btn sm"
                                   href="<?= site_url('driver/vehicles/edit/' . (int)$v['id']); ?>">
                                    Edit
                                </a>

                                <form action="<?= site_url('driver/vehicles/delete/' . (int)$v['id']); ?>"
                                      method="post"
                                      style="display:inline"
                                      onsubmit="return confirm('Delete this vehicle?');">
                                    <?= csrf_field() ?>
                                    <button class="btn danger sm" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
