<?php
/**
 * Vista: Driver · Edit Ride
 *
 * Espera:
 * - $ride         : array con datos del ride
 * - $vehicles     : array de vehículos del driver
 * - $selectedDays : array de días seleccionados (mon, tue, ...)
 * - $dayLabels    : ['mon'=>'Mon', ...]
 * - $msg, $error  : mensajes opcionales
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Aventones · Edit Ride</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilos_NewEditRide.css') ?>?v=3">
</head>
<body>
  <header class="main-header">
    <div class="logo-container">
      <img src="<?= base_url('assets/img/icono_carros.png') ?>" class="logo" alt="Logo">
      <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
      <a href="<?= site_url('driver/my-rides') ?>" class="active">My Rides</a>
      <a href="<?= site_url('driver/new-ride') ?>">New Ride</a>
      <a href="<?= site_url('driver/vehicles') ?>">Vehicles</a>
      <a href="<?= site_url('driver/bookings') ?>">Bookings</a>
      <div class="user-menu" style="margin-left:auto">
        <img src="<?= base_url('assets/img/user_icon.png') ?>" class="user-icon" alt="User Icon">
        <div class="user-dropdown">
          <a href="<?= site_url('logout') ?>">Logout</a>
          <a href="<?= site_url('profile/edit') ?>">Profile</a>
          <a href="<?= site_url('profile/configuration') ?>">Settings</a>
        </div>
      </div>
    </nav>
  </header>

  <main class="form-container">
    <h2>Edit Ride</h2>

    <?php if (!empty($error)): ?>
      <div class="msg err"><?= esc($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($msg)): ?>
      <div class="msg ok"><?= esc($msg) ?></div>
    <?php endif; ?>

    <form action="<?= site_url('driver/rides/update/' . (int)$ride['id']) ?>"
          method="post"
          class="grid2"
          autocomplete="off">

      <div>
        <label for="name">Ride Name (optional)</label>
        <input type="text" id="name" name="name"
               value="<?= esc($ride['name'] ?? '') ?>">
      </div>

      <div>
        <label for="vehicle_id">Vehicle</label>
        <select name="vehicle_id" id="vehicle_id" required>
          <option value="">Select vehicle</option>
          <?php foreach ($vehicles as $v): ?>
            <option value="<?= (int)$v['id'] ?>"
              <?= ((int)$v['id'] === (int)$ride['vehicle_id']) ? 'selected' : '' ?>>
              <?= esc($v['make'] . ' ' . $v['model'] . ' ' . $v['year']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="origin">Origin</label>
        <input type="text" id="origin" name="origin" required
               value="<?= esc($ride['origin']) ?>">
      </div>

      <div>
        <label for="destination">Destination</label>
        <input type="text" id="destination" name="destination" required
               value="<?= esc($ride['destination']) ?>">
      </div>

      <div style="grid-column:1 / -1">
        <label>Days</label>
        <div class="weekdays" style="display:flex; gap:10px; flex-wrap:wrap">
          <?php foreach ($dayLabels as $val => $label): ?>
            <label style="display:inline-flex; gap:6px; align-items:center; background:#0b1220; border:1px solid #1f2937; padding:8px 10px; border-radius:8px;">
              <input type="checkbox"
                     name="days[]"
                     value="<?= esc($val) ?>"
                     <?= in_array($val, $selectedDays, true) ? 'checked' : '' ?>>
              <?= esc($label) ?>
            </label>
          <?php endforeach; ?>
        </div>
        <small style="color:#9ca3af">Select one or more days.</small>
      </div>

      <div>
        <label for="departure_time">Time</label>
        <input type="time" id="departure_time" name="departure_time" required
               value="<?= esc(substr($ride['departure_time'], 0, 5)) ?>">
      </div>

      <div>
        <label for="seat_price">Seat Price (₡)</label>
        <input type="number" id="seat_price" name="seat_price" step="0.01" min="0"
               value="<?= esc((string)$ride['seat_price']) ?>" required>
      </div>

      <div>
        <label for="total_seats">Total Seats</label>
        <input type="number" id="total_seats" name="total_seats" min="1"
               value="<?= (int)$ride['total_seats'] ?>" required>
      </div>

      <div style="grid-column:1 / -1; text-align:right">
        <a class="btn" href="<?= site_url('driver/my-rides') ?>">Cancel</a>
        <button type="submit" class="btn primary">Save Changes</button>
      </div>
    </form>
  </main>
</body>
</html>
