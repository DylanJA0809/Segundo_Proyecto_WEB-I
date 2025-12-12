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
        <!-- Passwordless Login-->

        <div style="margin-top: 2rem;">

            <!-- Botón inicial -->
            <button
                type="button"
                class="login-btn"
                id="togglePasswordless"
                style="width: 100%;"
            >
                Log in with a link
            </button>

            
            <div
                id="passwordlessBox"
                style="display: none; margin-top: 1rem;"
            >
                <h3>Log in with a link</h3>

                <p style="font-size: 0.9rem; color: #555;">
                    Enter your email and we will send you a link to access without a password.
                </p>

                <form
                    method="post"
                    action="<?= site_url('passwordless/send-link') ?>"
                    style="margin-top: 0.5rem;"
                >
                    <label for="pwless_email">Email</label>
                    <input
                        type="email"
                        id="pwless_email"
                        name="pwless_email"
                        required
                    >

                    <button
                        type="submit"
                        class="login-btn"
                        style="margin-top: 0.5rem;"
                    >
                        Send me the login link
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePasswordless').addEventListener('click', function () {
    const box = document.getElementById('passwordlessBox');

    if (box.style.display === 'none') {
        box.style.display = 'block';
        this.textContent = 'Hide login with link';
    } else {
        box.style.display = 'none';
        this.textContent = 'Log in with a link';
    }
});
</script>


<?= $this->endSection() ?>
