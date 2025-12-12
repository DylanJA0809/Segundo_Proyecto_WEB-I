<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>

<div class="centered-container">
    <div class="login-container">
        <div class="logoText-container">
            <img src="<?= base_url('assets/img/icono_carros.png') ?>" alt="Logo" class="logo">
            <h1>AVENTONES</h1>
        </div>

        <button class="google-btn">Sign in with Google</button>
        <p class="or">Or</p>
        <form id="login-Form" method="post" action="<?= site_url('login') ?>">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <p class="register">
                Not an user?
                <a href="<?= site_url('register') ?>">Register Now</a>
            </p>

            <p class="register">
                Want any ride?
                <a href="<?= site_url('public-rides') ?>">Search Now</a>
            </p>

            <button type="submit" class="login-btn" id="login-btn">Login</button>
        </form>

        <div id="login-message"></div>
    </div>
</div>

<?= $this->endSection() ?>
