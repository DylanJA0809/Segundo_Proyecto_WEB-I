<?php
/**
 * Vista: Driver · My Rides
 * Espera: $rides (array de rides)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Aventones · My Rides</title>

  <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>?v=4">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>?v=4">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilo_MyRide.css') ?>?v=4">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilo_admin_users.css') ?>?v=4">
</head>
<body>
  <header class="main-header">
    <div class="logo-container">
      <img src="<?= base_url('assets/img/icono_carros.png') ?>" class="logo" alt="Logo">
      <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
      <a class="active" href="<?= site_url('driver/my-rides') ?>">Rides</a>
      <a href="<?= site_url('driver/rides/new-ride') ?>">New Ride</a>
      <a href="<?= site_url('driver/vehicles') ?>">Vehicles</a>
      <a href="<?= site_url('driver/bookings') ?>">Bookings</a>
      <div class="user-menu">
        <img src="<?= base_url('assets/img/user_icon.png') ?>" class="user-icon" alt="User Icon">
        <div class="user-dropdown">
          <a href="<?= site_url('profile/edit') ?>">Profile</a>
          <a href="<?= site_url('logout') ?>">Logout</a>
          <a href="<?= site_url('profile/configuration') ?>">Settings</a>
        </div>
      </div>
    </nav>
  </header>

  <main class="wrap">
    <h2>My Rides</h2>
    <?php if (!empty($error)): ?>
    <div class="msg err"><?= esc($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($msg)): ?>
    <div class="msg ok"><?= esc($msg) ?></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:14px;">
      <a class="btn primary" href="<?= site_url('driver/rides/new-ride') ?>">+ New Ride</a>
    </div>

    <div class="card">
      <?php if (empty($rides)): ?>
        <p class="msg">You don't have rides yet. Create your first one.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>From</th>
              <th>To</th>
              <th>Date & Time</th>
              <th>Seats</th>
              <th>Vehicle</th>
              <th>Fee</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
            $dayMap = [
              'mon' => 'Mon',
              'tue' => 'Tue',
              'wed' => 'Wed',
              'thu' => 'Thu',
              'fri' => 'Fri',
              'sat' => 'Sat',
              'sun' => 'Sun',
            ];
          ?>
          <?php foreach ($rides as $r): ?>
            <tr>
              <td><?= esc($r['name'] ?: '-') ?></td>
              <td><?= esc($r['origin']) ?></td>
              <td><?= esc($r['destination']) ?></td>
              <td>
                <?php
                  $days   = !empty($r['days_set']) ? explode(',', $r['days_set']) : [];
                  $labels = [];
                  foreach ($days as $d) {
                      $key = strtolower(trim($d));
                      $labels[] = $dayMap[$key] ?? $d;
                  }
                  echo esc(implode(', ', $labels));
                ?>
                &nbsp;·&nbsp;
                <?= $r['departure_time']
                    ? esc(substr($r['departure_time'], 0, 5))
                    : '—'
                ?>
              </td>
              <td><?= (int) $r['available_seats'] ?> / <?= (int) $r['total_seats'] ?></td>
              <td>
                <?php
                  $vehicleText = trim(
                      ($r['make'] ?? '') . ' ' .
                      ($r['model'] ?? '') . ' ' .
                      ($r['year'] ?? '')
                  );
                  echo $vehicleText !== '' ? esc($vehicleText) : '-';
                ?>
              </td>
              <td>
                <?php if ($r['seat_price'] !== null): ?>
                  <?php
                    $fee = number_format((float)$r['seat_price'], 0, ',', '.');
                    echo '₡' . esc($fee);
                  ?>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
              <td class="actions">
                <a class="btn" href="<?= site_url('driver/rides/edit/' . (int) $r['id']) ?>">Edit</a>

                <form method="post"
                      action="<?= site_url('driver/rides/delete/' . (int) $r['id']) ?>"
                      onsubmit="return confirm('Delete this ride?');"
                      style="display:inline;">
                  <button class="btn danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>

  <footer class="footer">
    <div class="footer-links">
      <a href="<?= site_url('public-rides') ?>">Home</a> |
      <a href="<?= site_url('driver/my-rides') ?>">Rides</a> |
      <a href="<?= site_url('driver/bookings') ?>">Bookings</a>
    </div>
    <p class="footer-copy">© Aventones.com</p>
  </footer>
</body>
</html>
