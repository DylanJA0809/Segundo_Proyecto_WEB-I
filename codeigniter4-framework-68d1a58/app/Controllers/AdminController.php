<?php

namespace App\Controllers;

use App\Models\UserModel;

class AdminController extends BaseController
{
    //chequear que sea admin
    private function requireAdmin()
    {
        if (!session('logged_in') || session('user_role') !== 'admin') {
            return false;
        }
        return true;
    }

    // Listado de usuarios
    public function users()
    {
        if (!$this->requireAdmin()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be an admin to access this section.')
            );
        }

        $request = $this->request;
        $role    = trim($request->getGet('role') ?? '');
        $status  = trim($request->getGet('status') ?? '');

        $userModel = new UserModel();
        $builder   = $userModel;

        if ($role !== '') {
            $builder = $builder->where('role', $role);
        }
        if ($status !== '') {
            $builder = $builder->where('status', $status);
        }

        $users = $builder->orderBy('id', 'ASC')->findAll();

        $data = [
            'users'  => $users,
            'role'   => $role,
            'status' => $status,
            'msg'    => $request->getGet('msg') ?? '',
            'error'  => $request->getGet('error') ?? '',
        ];

        return view('admin/users', $data);
    }

    // Crear nuevo admin
    public function createAdmin()
    {
        if (!$this->requireAdmin()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be an admin to access this section.')
            );
        }

        $request = $this->request;

        if (strtoupper($request->getMethod()) !== 'POST') {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('Invalid request method')
            );
        }

        // Datos del formulario
        $role   = 'admin'; // fijo
        $first  = trim($request->getPost('first_name') ?? '');
        $last   = trim($request->getPost('last_name') ?? '');
        $nid    = trim($request->getPost('national_id') ?? '');
        $birth  = trim($request->getPost('birth_date') ?? '');
        $email  = trim($request->getPost('email') ?? '');
        $phone  = trim($request->getPost('phone') ?? '');
        $pass1  = $request->getPost('password')  ?? '';
        $pass2  = $request->getPost('password2') ?? '';

        // Validaciones 
        if ($first === '' || $last === '' || $nid === '' || $birth === '' ||
            $email === '' || $phone === '' || $pass1 === '' || $pass2 === '') {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('Please fill in all required fields.')
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('Invalid email address.')
            );
        }

        if ($pass1 !== $pass2) {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('Passwords do not match.')
            );
        }

        if (strlen($pass1) < 8) {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('Password must be at least 8 characters long.')
            );
        }

        $userModel = new UserModel();

        // Verificar email único
        $existing = $userModel->where('email', $email)->first();
        if ($existing) {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('This email is already registered.')
            );
        }

        // Foto
        $photoPath = null;
        $file = $request->getFile('photo');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                return redirect()->to(
                    site_url('admin/users') . '?error=' . urlencode('Unsupported image format. Allowed: JPG, PNG, WebP.')
                );
            }

            // Carpeta: public/Img/users
            $uploadDir = FCPATH . 'Img/users';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newName = uniqid('user_', true) . '.' . $ext;
            try {
                $file->move($uploadDir, $newName);
            } catch (\Throwable $e) {
                return redirect()->to(
                    site_url('admin/users') . '?error=' . urlencode('Failed to upload the image.')
                );
            }

            $photoPath = 'Img/users/' . $newName; // ruta relativa usada en el front
        }

        // Hash de la contraseña
        $hash = password_hash($pass1, PASSWORD_BCRYPT);

        // Insert en la BD (usando UserModel)
        $data = [
            'role'         => $role,
            'status'       => 'active', // los admins se crean activos
            'first_name'   => $first,
            'last_name'    => $last,
            'national_id'  => $nid,
            'birth_date'   => $birth,
            'email'        => $email,
            'phone'        => $phone,
            'photo_path'   => $photoPath,
            'password_hash'=> $hash,
        ];

        if (!$userModel->insert($data)) {
            $errors = $userModel->errors();
            $msgErr = 'Database error.';
            if (!empty($errors)) {
                $msgErr .= ' ' . implode(' ', $errors);
            }

            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode($msgErr)
            );
        }

        return redirect()->to(
            site_url('admin/users') . '?msg=' . urlencode('Administrator created successfully.')
        );
    }

    public function changeUserStatus($id)
    {
        // Solo admins
        if (!$this->requireAdmin()) {
            return redirect()->to(
                site_url('login') . '?error=' . urlencode('You must be an admin to access this section.')
            );
        }

        $request = $this->request;

        if (strtoupper($request->getMethod()) !== 'POST') {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('Invalid request method')
            );
        }

        $userId    = (int) $id;
        $newStatus = $request->getPost('new_status');

        if ($userId <= 0 || !in_array($newStatus, ['active', 'inactive'], true)) {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('Invalid data.')
            );
        }

        // No permitir que el admin se desactive a sí mismo
        $currentUserId = (int) (session('user_id') ?? 0);
        if ($userId === $currentUserId && $newStatus === 'inactive') {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('You cannot deactivate your own account.')
            );
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode('User not found.')
            );
        }

        // Actualizar status
        $updated = $userModel->update($userId, ['status' => $newStatus]);

        if ($updated === false) {
            $errors = $userModel->errors();
            $msgErr = 'Database error.';
            if (!empty($errors)) {
                $msgErr .= ' ' . implode(' ', $errors);
            }

            return redirect()->to(
                site_url('admin/users') . '?error=' . urlencode($msgErr)
            );
        }

        return redirect()->to(
            site_url('admin/users') . '?msg=' . urlencode("User status updated to {$newStatus}.")
        );
    }
    
    // Reporte de búsquedas
    public function searchReport(){
        $role = session()->get('user_role');
        if ($role !== 'admin') {
            return redirect()->to('/login');
        }

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $logModel = new \App\Models\SearchLogModel();

        $builder = $logModel
            ->select('search_logs.*, users.first_name, users.last_name, users.email')
            ->join('users', 'users.id = search_logs.user_id', 'left');

        if (!empty($from)) {
            $builder->where('search_logs.created_at >=', $from . ' 00:00:00');
        }

        if (!empty($to)) {
            $builder->where('search_logs.created_at <=', $to . ' 23:59:59');
        }

        $logs = $builder->orderBy('search_logs.created_at', 'DESC')->findAll();

        return view('admin/search_report', [
            'logs' => $logs,
            'from' => $from,
            'to'   => $to,
        ]);
    }


}
