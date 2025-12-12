<?php

namespace App\Controllers;

use App\Models\UserModel;
use Config\Database;

class AuthController extends BaseController
{
    // ------------------------ LOGIN ------------------------
    public function login()
    {
        $data['title'] = 'Aventones - Login';
        return view('auth/login', $data);
    }

    // Procesar el login
    public function doLogin()
    {
        $request = $this->request;

        if ($request->getMethod() !== 'POST') {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('Invalid request method')
            );
        }

        $email    = trim($request->getPost('email') ?? '');
        $password = trim($request->getPost('password') ?? '');

        if ($email === '' || $password === '') {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('Please fill in all fields.')
            );
        }

        $userModel = new UserModel();
        $user = $userModel
            ->select('id, role, status, first_name, last_name, email, password_hash')
            ->where('email', $email)
            ->first();

        if (!$user) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('User not found.')
            );
        }

        if ($user['status'] === 'pending') {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('Your account is pending activation.')
            );
        }

        if ($user['status'] === 'inactive') {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('Your account is inactive.')
            );
        }

        if (!password_verify($password, $user['password_hash'])) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('Incorrect password.')
            );
        }

        // Sesión en CodeIgniter (equivalente a $_SESSION[...] = ...)
        session()->set([
            'user_id'     => $user['id'],
            'user_role'   => $user['role'],
            'user_name'   => $user['first_name'] . ' ' . $user['last_name'],
            'user_email'  => $user['email'],
            'logged_in'   => true,
        ]);
        
        // Redirigir según rol
        switch ($user['role']) {
            case 'admin':
                return redirect()->to('/admin/users');
            case 'driver':
                return redirect()->to(site_url('driver/my-rides'));
            case 'passenger':
                return redirect()->to(site_url('passenger/search-rides'));
        default:
            return redirect()->to(site_url('login') . '?error=' . urlencode('Unknown role.'));
        }
    }

    // Cerrar sesión
    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('login'));
    }

    // ------------------- REGISTRO PASAJERO ----------------
    public function registerUser()
    {
        $data['title'] = 'Aventones - Register User';
        return view('auth/register_user', $data);
    }

    public function doRegisterUser()
    {
        return $this->handleRegister('passenger');
    }

    // ------------------- REGISTRO DRIVER ------------------
    public function registerDriver()
    {
        $data['title'] = 'Aventones - Register Driver';
        return view('auth/register_driver', $data);
    }

    public function doRegisterDriver()
    {
        return $this->handleRegister('driver');
    }

    // ------------------- LÓGICA COMÚN REGISTRO ------------------
    private function handleRegister(string $role)
{
    $request = $this->request;

    if ($request->getMethod() !== 'POST') {
        return redirect()->to(site_url('login'))
                         ->with('error', 'Invalid request method');
    }

    $role = strtolower(trim($role));
    if (!in_array($role, ['passenger', 'driver'], true)) {
        return redirect()->to(site_url('login'))
                         ->with('error', 'Invalid role');
    }

    $formBackRoute = ($role === 'passenger') ? 'register' : 'register/driver';

    // === Datos del formulario ===
    $first_name  = trim($request->getPost('first_name')  ?? '');
    $last_name   = trim($request->getPost('last_name')   ?? '');
    $national_id = trim($request->getPost('national_id') ?? '');
    $birth_date  = trim($request->getPost('birth_date')  ?? '');
    $email       = trim($request->getPost('email')       ?? '');
    $phone       = trim($request->getPost('phone')       ?? '');
    $password    = $request->getPost('password')  ?? '';
    $password2   = $request->getPost('password2') ?? '';

    // === Validaciones básicas ===
    if ($first_name === '' || $last_name === '' || $national_id === '' ||
        $birth_date === '' || $email === '' || $phone === '' ||
        $password === '' || $password2 === '') {

        return redirect()->to(site_url($formBackRoute))
                         ->with('error', 'Please fill in all required fields.')
                         ->withInput();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return redirect()->to(site_url($formBackRoute))
                         ->with('error', 'Invalid email address.')
                         ->withInput();
    }

    if ($password !== $password2) {
        return redirect()->to(site_url($formBackRoute))
                         ->with('error', 'Passwords do not match.')
                         ->withInput();
    }

    if (strlen($password) < 8) {
        return redirect()->to(site_url($formBackRoute))
                         ->with('error', 'Password must be at least 8 characters long.')
                         ->withInput();
    }

    // input type="date" ya viene como YYYY-MM-DD, no le hacemos regex extra si no quieres
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $db = \Config\Database::connect();
    $builderUsers  = $db->table('users');

    // === Verificar email único ===
    $exists = $builderUsers->select('id')
                           ->where('email', $email)
                           ->get()
                           ->getRowArray();

    if ($exists) {
        return redirect()->to(site_url($formBackRoute))
                         ->with('error', 'This email is already registered.')
                         ->withInput();
    }

    // === Foto opcional ===
    $photoPath = null;
    $file = $request->getFile('photo');

    if ($file && $file->isValid() && !$file->hasMoved() && $file->getName() !== '') {
        $ext = strtolower($file->getExtension());
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowedExt, true)) {
            return redirect()->to(site_url($formBackRoute))
                             ->with('error', 'Unsupported image format. Allowed: JPG, PNG, WebP.')
                             ->withInput();
        }

        $mime = $file->getMimeType();
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime, $allowedMime, true)) {
            return redirect()->to(site_url($formBackRoute))
                             ->with('error', 'Invalid image content type.')
                             ->withInput();
        }

        $uploadDir = FCPATH . 'assets/img/users';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newName = uniqid('user_', true) . '.' . $ext;

        if (!$file->move($uploadDir, $newName)) {
            return redirect()->to(site_url($formBackRoute))
                             ->with('error', 'Failed to upload the image.')
                             ->withInput();
        }

        // Ruta relativa desde public
        $photoPath = 'assets/img/users/' . $newName;
    }

    // === Transacción: usuario + token ===
    $db->transStart();

    try {
        // 1) Insert usuario (AQUÍ SÍ PASAMOS ARRAY → no hay error de "set")
        $builderUsers->insert([
            'role'          => $role,
            'status'        => 'pending',
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'national_id'   => $national_id,
            'birth_date'    => $birth_date,
            'email'         => $email,
            'phone'         => $phone,
            'photo_path'    => $photoPath,
            'password_hash' => $passwordHash,
        ]);

        $userId = $db->insertID();
        if (!$userId) {
            throw new \RuntimeException('Could not insert user.');
        }

        // 2) Generar token y guardar
        [$rawToken, $tokenHash] = $this->generateToken();
        $expiresAt = (new \DateTime('+24 hours'))->format('Y-m-d H:i:s');

        $builderTokens = $db->table('email_verification_tokens');
        $builderTokens->insert([
            'user_id'    => $userId,
            'token'      => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \RuntimeException('Transaction failed.');
        }

        // ------------------------
        //   ENVÍO DE CORREO
        // ------------------------

        $baseUrl = 'http://mvccodeigniter.isw:8080/';
        $activateLink = $baseUrl . 'activate?email=' . urlencode($email) . '&token=' . $rawToken;

        $subject = 'Activa tu cuenta en Aventones';
        $html = "
        <h2>¡Hola, {$first_name}!</h2>
        <p>Gracias por registrarte en <b>Aventones</b>. Para activar tu cuenta, haz clic en el siguiente enlace:</p>
        <p><a href='{$activateLink}'>Activar cuenta</a></p>
        <p>Si no puedes hacer clic, copia y pega esta URL en tu navegador:<br>{$activateLink}</p>
        <p><small>El enlace expira en 24 horas.</small></p>
        ";
        $text = "Hola, {$first_name}. Activa tu cuenta con este enlace (expira en 24h): {$activateLink}";

        $mailer = new \App\Libraries\Mailer();
        $sent = $mailer->send($email, "{$first_name} {$last_name}", $subject, $html, $text);

        // Alert + redirect según si se envió o no
        if (!$sent) {
            echo "<script>
                alert('Your account was created, but the activation email could not be sent. Please try again later.');
                window.location.href = '" . site_url('login') . "';
            </script>";
            exit;
        }

        echo "<script>
            alert('Registration successful! Please check your email to activate your account.');
            window.location.href = '" . site_url('login') . "';
        </script>";
        exit;

    } catch (\Throwable $e) {
        $db->transRollback();

        return redirect()->to(site_url($formBackRoute))
                         ->with('error', 'An error occurred while creating your account. Please try again.')
                         ->withInput();
    }
}

    // Genera token y hash
    private function generateToken(): array
    {
        $rawToken  = bin2hex(random_bytes(32));      // 64 chars hex
        $tokenHash = hash('sha256', $rawToken);      // hash
        return [$rawToken, $tokenHash];
    }

    public function activate()
    {
        $email = $this->request->getGet('email');
        $token = $this->request->getGet('token');

        if (!$email || !$token) {
            echo "<script>
                alert('Invalid activation link.');
                window.location.href = '" . site_url('login') . "';
            </script>";
            exit;
        }

        $db = \Config\Database::connect();
        $builderTokens = $db->table('email_verification_tokens');
        $builderUsers  = $db->table('users');

        $tokenHash = hash('sha256', $token);

        // Buscar token + usuario
        $row = $builderTokens
            ->select('email_verification_tokens.*, users.id AS user_id, users.email, users.status')
            ->join('users', 'users.id = email_verification_tokens.user_id')
            ->where('users.email', $email)
            ->where('email_verification_tokens.token', $tokenHash)
            ->get()
            ->getRowArray();

        if (!$row) {
            echo "<script>
                alert('Invalid or already used activation link.');
                window.location.href = '" . site_url('login') . "';
            </script>";
            exit;
        }

        // Verificar expiración
        $now       = new \DateTime();
        $expiresAt = new \DateTime($row['expires_at']);

        if ($now > $expiresAt) {
            echo "<script>
                alert('This activation link has expired. Please register again.');
                window.location.href = '" . site_url('login') . "';
            </script>";
            exit;
        }

        // Si ya está activo, marcamos token como usado y salimos
        if ($row['status'] === 'active') {

            // Marcar token como usado
            $builderTokens
                ->where('id', $row['id'])
                ->update(['used_at' => date('Y-m-d H:i:s')]);

            echo "<script>
                alert('Your account is already active. You can log in now.');
                window.location.href = '" . site_url('login') . "';
            </script>";
            exit;
        }

        // Activar usuario
        $db->transStart();

        $builderUsers
            ->where('id', $row['user_id'])
            ->update([
                'status'     => 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        $builderTokens
            ->where('id', $row['id'])
            ->update([
                'used_at' => date('Y-m-d H:i:s')
            ]);

        $db->transComplete();

        echo "<script>
            alert('Your account has been activated successfully! You can now log in.');
            window.location.href = '" . site_url('login') . "';
        </script>";
        exit;
    }

}
