<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin · Search Report</title>

  <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>?v=4">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilo_admin_users.css') ?>?v=4">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilo_Login_Register.css') ?>?v=4">
</head>
<body>

  <header class="admin-header">
    <div class="brand">
      <img src="<?= base_url('assets/img/icono_carros.png') ?>" alt="Aventones" />
      <span>Aventones · Admin</span>
    </div>
    <nav class="nav-actions">
      <span class="who"><?= esc(session('user_name') ?? 'Admin') ?></span>
      <a href="<?= site_url('admin/users') ?>" class="btn ghost sm">
        User Management
      </a>
      <a class="btn ghost sm" href="<?= site_url('logout') ?>">Logout</a>
    </nav>
  </header>

  <div class="wrap">
    <h1>Admin · Search Report</h1>

    <!-- Filtros -->
    <div class="card">
      <h2>Search Filters</h2>

      <form class="toolbar" method="get" action="<?= site_url('admin/search-report') ?>">
        <label>
          From
          <input type="date" name="from" value="<?= esc($from ?? '') ?>">
        </label>

        <label>
          To
          <input type="date" name="to" value="<?= esc($to ?? '') ?>">
        </label>

        <button class="btn primary" type="submit">Apply</button>
        <a class="btn" href="<?= site_url('admin/search-report') ?>">Clear</a>
      </form>
    </div>

    <!-- Tabla de resultados -->
    <div class="card">
      <h2>Search Results</h2>

      <table class="table">
        <thead>
          <tr>
            <th>Date</th>
            <th>User</th>
            <th>Departure</th>
            <th>Arrival</th>
            <th>Results</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($logs)): ?>
          <?php foreach ($logs as $log): ?>
            <tr>
              <td><?= esc($log['created_at']) ?></td>
              <td>
                <?php if (!empty($log['user_id'])): ?>
                  <?= esc(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?><br>
                  <small><?= esc($log['email'] ?? '-') ?></small>
                <?php else: ?>
                  Public
                <?php endif; ?>
              </td>
              <td><?= esc($log['departure']) ?></td>
              <td><?= esc($log['arrival']) ?></td>
              <td><?= esc($log['results_count']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" style="text-align:center; opacity:.8;">
              No searches found for this date range.
            </td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>

