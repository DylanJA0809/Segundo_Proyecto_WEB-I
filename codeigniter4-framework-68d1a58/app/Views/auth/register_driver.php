<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<div class="centered-container">
    <div class="register-container">
        <div class="logo-container">
            <img src="<?= base_url('assets/img/icono_carros.png') ?>" class="logo" alt="Logo">
            <h1>AVENTONES</h1>
        </div>

        <br>
        <h2>Driver Registration</h2>

        <!-- Ruta MVC para registrar chofer -->
        <form id="register-form" method="post" action="<?= site_url('register/driver') ?>" enctype="multipart/form-data">
            <input type="hidden" name="role" value="driver">

            <div class="form-row">
                <div class="form-group">
                    <label for="first-name">First Name</label>
                    <input type="text" id="first-name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last-name">Last Name</label>
                    <input type="text" id="last-name" name="last_name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="national_id">ID Number</label>
                    <input type="text" id="national_id" name="national_id" required>
                </div>
                <div class="form-group">
                    <label for="birthdate">Date of Birth</label>
                    <input type="date" id="birthdate" name="birth_date" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password2">Repeat Password</label>
                    <input type="password" id="password2" name="password2" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <div class="custom-file-upload">
                        <label for="photo" class="file-label">Select Personal Photo</label>
                        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                        <span id="file-name">No file selected</span>
                    </div>
                </div>
            </div>
            
            <p class="login-links">
                Already a driver? 
                <a href="<?= site_url('login') ?>">Login here</a> |
                Register as passenger? 
                <a href="<?= site_url('register') ?>">Click here</a>
            </p>

            <button type="submit" class="signup-btn">Sign up</button>
        </form>

        <footer class="footer">
            <div class="footer-links">
                <a href="#">Home</a> |
                <a href="#">Rides</a> |
                <a href="#">Bookings</a> |
                <a href="#">Settings</a> |
                <a href="<?= site_url('login') ?>">Login</a> |
                <a href="<?= site_url('register') ?>">Register</a>
            </div>
            <p class="footer-copy">© Aventones.com</p>
        </footer>
    </div>
</div>

<?= $this->endSection() ?>
