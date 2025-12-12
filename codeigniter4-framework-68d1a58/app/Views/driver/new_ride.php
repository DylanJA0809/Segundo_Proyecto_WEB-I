<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aventones · New Ride</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>?v=4">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>?v=4">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilos_NewEditRide.css'); ?>?v=4">
</head>
<body>
<header class="main-header">
    <div class="logo-container">
        <img src="<?= base_url('Img/icono_carros.png'); ?>" class="logo" alt="Logo">
        <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
        <a href="<?= site_url('driver/my-rides'); ?>">My Rides</a>
        <a href="<?= site_url('driver/rides/new'); ?>" class="active">New Ride</a>
        <a href="<?= site_url('driver/vehicles'); ?>">Vehicles</a>
        <a href="<?= site_url('driver/bookings'); ?>">Bookings</a>

        <div class="search-container">
            <div class="user-menu" style="margin-left:auto">
                <img src="<?= base_url('Img/user_icon.png'); ?>" class="user-icon" alt="User Icon">
                <div class="user-dropdown">
                    <a href="<?= site_url('logout'); ?>">Logout</a>
                    <a href="<<?= site_url('profile/edit') ?>">Profile</a>
                    <a href="<?= site_url('profile/configuration') ?>">Settings</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main class="form-container">
    <h2>Create a New Ride</h2>

    <?php if (!empty($error)): ?>
        <div class="msg err"><?= esc($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($msg)): ?>
        <div class="msg ok"><?= esc($msg) ?></div>
    <?php endif; ?>

    <form action="<?= site_url('driver/rides/store'); ?>" method="post" class="grid2" autocomplete="off">
        <?= csrf_field() ?>

        <div>
            <label for="name">Ride Name (optional)</label>
            <input
                type="text"
                id="name"
                name="name"
                placeholder="Morning commute"
                value="<?= esc(old('name')) ?>"
            >
        </div>

        <div>
            <label for="vehicle_id">Vehicle</label>
            <select name="vehicle_id" id="vehicle_id" required>
                <option value="">Select vehicle</option>
                <?php if (!empty($vehicles)): ?>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= (int)$v['id']; ?>"
                            <?= (old('vehicle_id') == $v['id']) ? 'selected' : '' ?>>
                            <?= esc($v['make'] . ' ' . $v['model'] . ' ' . $v['year']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div>
            <label for="origin">Origin</label>
            <input
                type="text"
                id="origin"
                name="origin"
                required
                value="<?= esc(old('origin')) ?>"
            >
        </div>

        <div>
            <label for="destination">Destination</label>
            <input
                type="text"
                id="destination"
                name="destination"
                required
                value="<?= esc(old('destination')) ?>"
            >
        </div>

        <div style="grid-column:1 / -1">
            <label>Days</label>
            <div class="weekdays" style="display:flex; gap:10px; flex-wrap:wrap">
                <?php
                $days = ['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'];
                $oldDays = (array) (old('days') ?? []);
                ?>
                <?php foreach ($days as $val => $label): ?>
                    <label style="display:inline-flex; gap:6px; align-items:center; background:#0b1220; border:1px solid #1f2937; padding:8px 10px; border-radius:8px;">
                        <input
                            type="checkbox"
                            name="days[]"
                            value="<?= esc($val) ?>"
                            <?= in_array($val, $oldDays, true) ? 'checked' : '' ?>
                        >
                        <?= esc($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <small style="color:#9ca3af">Select one or more days.</small>
        </div>

        <div>
            <label for="departure_time">Time</label>
            <input
                type="time"
                id="departure_time"
                name="departure_time"
                required
                value="<?= esc(old('departure_time')) ?>"
            >
        </div>

        <div>
            <label for="seat_price">Seat Price (₡)</label>
            <input
                type="number"
                id="seat_price"
                name="seat_price"
                step="0.01"
                min="0"
                required
                value="<?= esc(old('seat_price')) ?>"
            >
        </div>

        <div>
            <label for="total_seats">Total Seats</label>
            <input
                type="number"
                id="total_seats"
                name="total_seats"
                min="1"
                required
                value="<?= esc(old('total_seats')) ?>"
            >
        </div>

        <div style="grid-column:1 / -1;text-align:right">
            <button type="submit" class="btn primary">Create Ride</button>
            <a class="btn" href="<?= site_url('driver/my-rides'); ?>">Cancel</a>
        </div>
    </form>
</main>
</body>
</html>
