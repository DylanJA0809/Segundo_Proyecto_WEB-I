<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - User Management</title>
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
      <a href="<?= site_url('admin/search-report') ?>" class="btn ghost sm">
        Search Report
      </a>
      <a class="btn ghost sm" href="<?= site_url('logout') ?>">Logout</a>
    </nav>
  </header>

  <div class="wrap">
    <h1>Admin · User Management</h1>

    <?php if (!empty($msg)): ?>
      <div class="msg ok"><?= esc($msg) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="msg err"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="row">
      <!-- crear nuevo admin -->
      <div class="card">
        <h2>Create Administrator</h2>
        <form method="post"
              action="<?= site_url('admin/users/create') ?>"
              enctype="multipart/form-data"
              class="grid2">
          <input type="hidden" name="role" value="admin">
          <div>
            <label>First Name</label>
            <input type="text" name="first_name" required>
          </div>
          <div>
            <label>Last Name</label>
            <input type="text" name="last_name" required>
          </div>
          <div>
            <label>ID Number</label>
            <input type="text" name="national_id" required>
          </div>
          <div>
            <label>Birth Date</label>
            <input type="date" name="birth_date" required>
          </div>
          <div>
            <label>Email</label>
            <input type="email" name="email" required>
          </div>
          <div>
            <label>Phone</label>
            <input type="tel" name="phone" required>
          </div>
          <div>
            <label>Password</label>
            <input type="password" name="password" required>
          </div>
          <div>
            <label>Repeat Password</label>
            <input type="password" name="password2" required>
          </div>
          <div style="grid-column:1 / -1" class="custom-file-upload">
            <label for="photo" class="file-label">Select Personal Photo</label>
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
            <span id="file-name">No file selected</span>
          </div>
          <div style="grid-column:1 / -1;text-align:right">
            <button class="btn primary" type="submit">Create Admin</button>
          </div>
        </form>
      </div>

      <!-- Lista de usuarios -->
      <div class="card">
        <div class="section-head">
          <h2>Users</h2>
        </div>

        <!-- Filtros -->
        <form class="toolbar" method="get" action="<?= site_url('admin/users') ?>">
          <label>Role
            <select name="role">
              <option value="">All</option>
              <option value="admin"     <?= ($role ?? '') === 'admin'     ? 'selected' : '' ?>>admin</option>
              <option value="driver"    <?= ($role ?? '') === 'driver'    ? 'selected' : '' ?>>driver</option>
              <option value="passenger" <?= ($role ?? '') === 'passenger' ? 'selected' : '' ?>>passenger</option>
            </select>
          </label>
          <label>Status
            <select name="status">
              <option value="">All</option>
              <option value="active"   <?= ($status ?? '') === 'active'   ? 'selected' : '' ?>>active</option>
              <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>inactive</option>
              <option value="pending"  <?= ($status ?? '') === 'pending'  ? 'selected' : '' ?>>pending</option>
            </select>
          </label>
          <button class="btn" type="submit">Filter</button>
          <a class="btn" href="<?= site_url('admin/users') ?>">Clear</a>
        </form>

        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Phone</th>
              <th>Birth</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!empty($users)): ?>
            <?php foreach ($users as $u): ?>
              <tr>
                <td><?= (int) $u['id'] ?></td>
                <td><?= esc($u['first_name'] . ' ' . $u['last_name']) ?></td>
                <td><?= esc($u['email']) ?></td>
                <td><?= esc($u['role']) ?></td>
                <td><span class="badge <?= esc($u['status']) ?>"><?= esc($u['status']) ?></span></td>
                <td><?= esc($u['phone']) ?></td>
                <td><?= esc($u['birth_date']) ?></td>
                <td class="actions">
                  <?php if ($u['status'] !== 'active'): ?>
                    <form method="post" action="<?= site_url('admin/users/change-status/' . (int) $u['id']) ?>">
                      <input type="hidden" name="new_status" value="active">
                      <button class="btn primary" type="submit">Activate</button>
                    </form>
                  <?php endif; ?>

                  <?php if ($u['status'] !== 'inactive'): ?>
                    <form method="post" action="<?= site_url('admin/users/change-status/' . (int) $u['id']) ?>">
                      <input type="hidden" name="new_status" value="inactive">
                      <button class="btn danger" type="submit">Deactivate</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" style="text-align:center; opacity:.8;">No users found.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>

      </div>
    </div>
  </div>
</body>
</html>
