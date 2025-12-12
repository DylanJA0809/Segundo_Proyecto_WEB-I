<?php
function e($s){ return esc((string)$s); }
$DEFAULT_AVATAR = base_url('Img/user_icon.png');
$PHOTO_REL = trim($user['photo_path'] ?? '');
$avatarUrl = $PHOTO_REL !== '' ? base_url(ltrim($PHOTO_REL, '/')) : $DEFAULT_AVATAR;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Aventones · Edit Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/estilos_NewEditRide.css?v=4') ?>">
  <script src="<?= base_url('assets/js/edit_profile_user.js') ?>" defer></script>
</head>
<body>
<header class="main-header">
  <div class="logo-container">
    <img src="<?= base_url('assets/img/icono_carros.png') ?>" class="logo" alt="Logo">
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
        <a class="active" href="<?= site_url('profile/edit') ?>">Profile</a>
        <a href="<?= site_url('profile/configuration') ?>">Settings</a>
      </div>
    </div>
  </nav>
</header>

<main class="form-container">
  <div class="profile-hero">
    <div class="profile-title">
      <h2>Edit Profile</h2>
      <small class="muted">Update your personal information</small>
    </div>
    <div class="avatar-wrap">
      <img class="avatar" src="<?= e($avatarUrl) ?>" alt="User photo">
      <label for="photo" class="btn sm ghost">Change</label>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="msg err"><?= e($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($msg)): ?>
    <div class="msg ok"><?= e($msg) ?></div>
  <?php endif; ?>

  <form action="<?= site_url('profile/update') ?>" method="post" enctype="multipart/form-data" class="grid2" autocomplete="off">
    <?= csrf_field() ?>

    <input type="hidden" name="current_photo" value="<?= e($user['photo_path'] ?? '') ?>">
    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">

    <div>
      <label>First Name</label>
      <input type="text" name="first_name" required value="<?= e($user['first_name']) ?>">
    </div>

    <div>
      <label>Last Name</label>
      <input type="text" name="last_name" required value="<?= e($user['last_name']) ?>">
    </div>

    <div>
      <label>ID Number</label>
      <input type="text" name="national_id" required value="<?= e($user['national_id']) ?>">
    </div>

    <div>
      <label>Birth Date</label>
      <input type="date" name="birth_date" required value="<?= e($user['birth_date']) ?>">
    </div>

    <div>
      <label>Email</label>
      <input type="email" name="email" required value="<?= e($user['email']) ?>">
    </div>

    <div>
      <label>Phone</label>
      <input type="tel" name="phone" required value="<?= e($user['phone']) ?>">
    </div>

    <div>
      <label>New Password (optional)</label>
      <input type="password" name="password" placeholder="Leave blank to keep current">
    </div>

    <div>
      <label>Repeat New Password</label>
      <input type="password" name="password2" placeholder="Repeat new password">
    </div>

    <div style="grid-column:1 / -1; text-align:right">
      <a class="btn" href="<?= (session('user_role') === 'driver') ? site_url('driver/my-rides') : site_url('passenger/search-rides') ?>">Cancel</a>
      <button class="btn primary" type="submit">Save Changes</button>
    </div>
  </form>
</main>
</body>
</html>
