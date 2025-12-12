<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Aventones · Ride Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Base URL para JS -->
  <meta name="base-url" content="<?= site_url() ?>">
  <script>
    window.RIDE_ID = <?= (int)($rideId ?? 0) ?>;
  </script>

  <!-- CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/estilos.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/estilo_nav_logo.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/layout_base.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/estilos_Ride_Details.css') ?>" />

  <!-- JS -->
  <script src="<?= base_url('assets/js/ride_details.js') ?>" defer></script>
  <script src="<?= base_url('assets/js/ride_request.js') ?>" defer></script>
</head>

<body>
<header class="main-header">
  <div class="logo-container">
    <img src="<?= base_url('assets/img/icono_carros.png') ?>" alt="Logo" class="logo">
    <h1>AVENTONES</h1>
  </div>

  <nav class="nav-bar">
    <a href="<?= site_url('passenger/search-rides') ?>" class="active">Home</a>
    <a href="<?= site_url('passenger/bookings') ?>">Bookings</a>

    <div class="search-container">
      <input type="text" placeholder="Search...">
      <div class="user-menu">
        <img src="<?= base_url('assets/img/user_icon.png') ?>" alt="User" class="user-icon">
        <div class="user-dropdown">
          <a href="<?= site_url('logout') ?>" id="Logout-btn">Logout</a>
          <a href="<?= site_url('profile/configuration') ?>">Settings</a>
          <a href="<?= site_url('profile/edit') ?>">Profile</a>
        </div>
      </div>
    </div>
  </nav>
</header>

<main class="ride-details-container">
  <h2 class="ride-title">Ride Details</h2>

  <div class="ride-profile">
    <img src="<?= base_url('assets/img/user_icon.png') ?>" alt="User" class="profile-img">
    <p class="username">Loading...</p>
  </div>

  <form class="ride-form" onsubmit="return false;">
    <div class="route-info">
      <label>Departure from <span>—</span></label>
      <label>Arrive To <span>—</span></label>
    </div>

    <div class="days-selection">
      <label class="days-label">Days</label>
      <div class="days-checkboxes">
        <label><input type="checkbox"> Mon</label>
        <label><input type="checkbox"> Tue</label>
        <label><input type="checkbox"> Wed</label>
        <label><input type="checkbox"> Thu</label>
        <label><input type="checkbox"> Fri</label>
        <label><input type="checkbox"> Sat</label>
        <label><input type="checkbox"> Sun</label>
      </div>
    </div>

    <div class="ride-fields">
      <div class="ride-field-inline">
        <label>Time</label>
        <input type="time" value="10:00" />
      </div>

      <!-- ESTE input lo usa ride_request.js -->
      <div class="ride-field-inline">
        <label>Seats (request)</label>
        <input type="number" id="seats-request" value="1" min="1" />
      </div>

      <div class="ride-field-inline">
        <label>Fee</label>
        <input type="number" value="0" min="0" />
      </div>
    </div>

    <fieldset class="vehicle-details">
      <legend>Vehicle Details</legend>
      <div class="vehicle-field">
        <label>Make</label>
        <input type="text" id="make" readonly>
      </div>
      <div class="vehicle-field">
        <label>Model</label>
        <input type="text" id="model" readonly>
      </div>
      <div class="vehicle-field">
        <label>Year</label>
        <input type="text" id="year" readonly>
      </div>
    </fieldset>

    <div class="form-actions">
      <a href="<?= site_url('passenger/search-rides') ?>" class="cancel-link">Cancel</a>
      <button type="button" id="request-btn" class="request-btn">Request</button>
    </div>
  </form>
</main>

<footer class="footer">
  <div class="footer-links">
    <a href="<?= site_url('passenger/search-rides') ?>">Home</a> |
    <a href="<?= site_url('login') ?>">Login</a> |
    <a href="<?= site_url('register') ?>">Register</a> |
    <a href="<?= site_url('logout') ?>">Logout</a>
  </div>
  <p class="footer-copy">© Aventones.com</p>
</footer>
</body>
</html>
