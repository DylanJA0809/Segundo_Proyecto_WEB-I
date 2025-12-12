<?php
// app/Views/driver/bookings.php

if (!function_exists('e')) {
    function e($s) {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// helper para los días
if (!function_exists('fmt_days')) {
    function fmt_days($set) {
        if (!$set) return '—';
        $map = [
            'mon' => 'Mon',
            'tue' => 'Tue',
            'wed' => 'Wed',
            'thu' => 'Thu',
            'fri' => 'Fri',
            'sat' => 'Sat',
            'sun' => 'Sun',
        ];
        $out = [];
        foreach (explode(',', strtolower($set)) as $d) {
            $out[] = $map[trim($d)] ?? $d;
        }
        return implode(', ', $out);
    }
}

/** @var array $reservations */
/** @var string $msg */
/** @var string $error */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aventones · Driver Bookings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS desde /public -->
    <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>?v=4">
    <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>?v=4">
    <link rel="stylesheet" href="<?= base_url('assets/css/driver_vehicles.css?v=2'); ?>">
</head>
<body>
<header class="main-header">
    <div class="logo-container">
        <img src="<?= base_url('assets/img/icono_carros.png'); ?>" class="logo" alt="Logo">
        <h1>AVENTONES</h1>
    </div>
    <nav class="nav-bar">
        <a href="<?= site_url('driver/my-rides'); ?>">My Rides</a>
        <a href="<?= site_url('driver/rides/new'); ?>">New Ride</a>
        <a href="<?= site_url('driver/vehicles'); ?>">Vehicles</a>
        <a href="<?= site_url('driver/bookings'); ?>" class="active">Bookings</a>
        <div class="user-menu" style="margin-left:auto">
            <img src="<?= base_url('assets/img/user_icon.png'); ?>" class="user-icon" alt="User">
            <div class="user-dropdown">
                <a href="<?= site_url('logout'); ?>">Logout</a>
                <a href="<?= site_url('profile/edit') ?>">Profile</a>
                <a href="<?= site_url('profile/configuration') ?>">Settings</a>
            </div>
        </div>
    </nav>
</header>

<main class="wrap">
    <?php if (!empty($error)): ?>
        <div class="msg err"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($msg)): ?>
        <div class="msg ok"><?= e($msg) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="section-head">
            <h2>Reservations</h2>
        </div>

        <?php if (empty($reservations)): ?>
            <p class="muted">No reservations yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Passenger</th>
                    <th>Ride</th>
                    <th>When</th>
                    <th>Vehicle</th>
                    <th>Seats</th>
                    <th>Status</th>
                    <th style="width:220px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reservations as $r): ?>
                    <tr>
                        <td><?= (int) $r['res_id'] ?></td>
                        <td>
                            <?= e($r['first_name'] . ' ' . $r['last_name']) ?><br>
                            <small class="muted"><?= e($r['email']) ?></small>
                        </td>
                        <td>
                            <?= e($r['ride_name'] ?: ($r['origin'] . ' → ' . $r['destination'])) ?><br>
                            <small class="muted">
                                <?= e($r['origin']) ?> → <?= e($r['destination']) ?>
                            </small>
                        </td>
                        <td>
                            <?= $r['departure_time'] ? e(substr($r['departure_time'], 0, 5)) : '—' ?><br>
                            <small class="muted"><?= e(fmt_days($r['days_set'])) ?></small>
                        </td>
                        <td>
                            <?php
                            $veh = trim(($r['make'] ?? '') . ' ' . ($r['model'] ?? '') . ' ' . ($r['year'] ?? ''));
                            echo $veh ? e($veh) : '—';
                            ?>
                        </td>
                        <td><?= (int) $r['res_seats'] ?></td>
                        <td>
                            <?php
                            $st = $r['res_status'];
                            $badgeClass = 'badge pending';
                            if ($st === 'accepted') $badgeClass = 'badge active';
                            if ($st === 'rejected' || $st === 'cancelled') $badgeClass = 'badge inactive';
                            ?>
                            <span class="<?= e($badgeClass) ?>"><?= e($st) ?></span>
                        </td>
                        <td class="actions">
                            <?php if ($r['res_status'] === 'pending'): ?>
                                <form action="<?= site_url('driver/bookings/change-status'); ?>" method="post" style="display:inline">
                                    <input type="hidden" name="reservation_id" value="<?= (int) $r['res_id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button class="btn primary sm" type="submit">Accept</button>
                                </form>
                                <form action="<?= site_url('driver/bookings/change-status'); ?>" method="post" style="display:inline">
                                    <input type="hidden" name="reservation_id" value="<?= (int) $r['res_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn danger sm" type="submit">Reject</button>
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
