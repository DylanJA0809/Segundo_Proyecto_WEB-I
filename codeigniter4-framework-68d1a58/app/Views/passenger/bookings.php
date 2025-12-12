<?php
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function fmt_days($set){
  if(!$set) return '—';
  $map = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];
  $out = [];
  foreach (explode(',', strtolower((string)$set)) as $d) { $out[] = $map[$d] ?? $d; }
  return implode(', ', $out);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Aventones · My Bookings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/driver_vehicles.css') ?>">
</head>
<body>
<header class="main-header">
  <div class="logo-container">
    <img src="<?= base_url('assets/img/icono_carros.png') ?>" class="logo" alt="Logo"><h1>AVENTONES</h1>
  </div>
  <nav class="nav-bar">
    <a href="<?= site_url('passenger/search-rides') ?>">Search Rides</a>
    <a href="<?= site_url('passenger/bookings') ?>" class="active">Bookings</a>

    <div class="user-menu" style="margin-left:auto">
      <img src="<?= base_url('assets/img/user_icon.png') ?>" class="user-icon" alt="User">
      <div class="user-dropdown">
        <a href="<?= site_url('logout') ?>">Logout</a>
        <a href="<?= site_url('profile/edit') ?>">Profile</a>
        <a href="<?= site_url('profile/configuration') ?>">Settings</a>
      </div>
    </div>
  </nav>
</header>

<main class="wrap">
  <?php if(!empty($error)): ?><div class="msg err"><?= e($error) ?></div><?php endif; ?>
  <?php if(!empty($msg)): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>

  <div class="card">
    <div class="section-head">
      <h2>My Reservations</h2>
    </div>

    <?php if(empty($rows)): ?>
      <p class="muted">You don't have reservations yet.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Driver</th>
            <th>Ride</th>
            <th>When</th>
            <th>Vehicle</th>
            <th>Seats</th>
            <th>Status</th>
            <th style="width:180px;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?= (int)$r['res_id'] ?></td>

            <td>
              <?= e(($r['driver_first_name'] ?? '').' '.($r['driver_last_name'] ?? '')) ?><br>
              <small class="muted"><?= e($r['driver_email'] ?? '') ?></small>
            </td>

            <td>
              <?php
                $rideTitle = $r['ride_name'] ?: (($r['origin'] ?? '').' → '.($r['destination'] ?? ''));
              ?>
              <?= e($rideTitle) ?><br>
              <small class="muted"><?= e($r['origin'] ?? '') ?> → <?= e($r['destination'] ?? '') ?></small>
            </td>

            <td>
              <?= !empty($r['departure_time']) ? e(substr($r['departure_time'],0,5)) : '—' ?><br>
              <small class="muted"><?= e(fmt_days($r['days_set'] ?? '')) ?></small>
            </td>

            <td>
              <?php
                $veh = trim(($r['make'] ?? '').' '.($r['model'] ?? '').' '.($r['year'] ?? ''));
                echo $veh ? e($veh) : '—';
              ?>
            </td>

            <td><?= (int)$r['res_seats'] ?></td>

            <td>
              <?php
                $st = (string)($r['res_status'] ?? '');
                $badgeClass = 'badge pending';
                if ($st==='accepted') $badgeClass='badge active';
                if (in_array($st, ['rejected','cancelled','completed'], true)) $badgeClass='badge inactive';
              ?>
              <span class="<?= $badgeClass ?>"><?= e($st) ?></span>
            </td>

            <td class="actions">
              <?php if (($r['res_status'] ?? '')==='pending' || ($r['res_status'] ?? '')==='accepted'): ?>
                <form action="<?= site_url('passenger/bookings/cancel/'.(int)$r['res_id']) ?>"
                      method="post"
                      onsubmit="return confirm('Do you want to cancel this reservation?')">
                  <?= csrf_field() ?>
                  <button class="btn danger sm" type="submit">Cancel</button>
                </form>
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
