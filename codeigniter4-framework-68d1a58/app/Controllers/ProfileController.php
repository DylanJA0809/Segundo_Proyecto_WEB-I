<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    private function requireLogin(): bool
    {
        return (bool) session('logged_in') && (int)session('user_id') > 0;
    }

    public function configuration()
    {
        if (!$this->requireLogin()) {
            return redirect()->to(site_url('login') . '?error=' . urlencode('Please login'));
        }

        return view('profile/configuration');
    }

    public function edit()
    {
        if (!$this->requireLogin()) {
            return redirect()->to(site_url('login') . '?error=' . urlencode('Please login'));
        }

        $userId = (int) session('user_id');
        $userModel = new UserModel();

        $user = $userModel->select('id, role, status, first_name, last_name, national_id, birth_date, email, phone, photo_path')
            ->where('id', $userId)
            ->first();

        if (!$user) {
            return redirect()->to(site_url('login') . '?error=' . urlencode('User not found'));
        }

        $data = [
            'user'  => $user,
            'msg'   => $this->request->getGet('msg') ?? '',
            'error' => $this->request->getGet('error') ?? '',
        ];

        return view('profile/edit_profile', $data);
    }

    // ---------------- API BIO ----------------

    public function apiGetBio()
    {
        if (!$this->requireLogin()) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'Invalid session']);
        }

        $userId = (int) session('user_id');
        $userModel = new UserModel();
        $row = $userModel->getBioById($userId);

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'User not found']);
        }

        return $this->response->setJSON([
            'ok' => true,
            'first_name' => $row['first_name'] ?? '',
            'bio' => $row['bio'] ?? '',
        ]);
    }

    public function apiUpdateBio()
    {
        if (!$this->requireLogin()) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'Invalid session']);
        }

        $userId = (int) session('user_id');
        $input = $this->request->getJSON(true) ?? [];
        $bio = trim((string)($input['bio'] ?? ''));

        $userModel = new UserModel();
        $result = $userModel->updateBioById($userId, $bio);

        if (!$result['ok']) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'error' => $result['error']]);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    // ---------------- UPDATE PROFILE ----------------

    public function update()
    {
        if (!$this->requireLogin()) {
            return redirect()->to(site_url('login') . '?error=' . urlencode('Please login'));
        }

        if (strtoupper($this->request->getMethod()) !== 'POST') {
            return redirect()->to(site_url('profile/edit') . '?error=' . urlencode('Invalid request method'));
        }

        $userId = (int) session('user_id');

        $input = $this->request->getPost();

        $photoFile = $this->request->getFile('photo');

        $userModel = new UserModel();
        $result = $userModel->updateProfile($userId, $input, $photoFile);

        if (!$result['ok']) {
            return redirect()->to(site_url('profile/edit') . '?error=' . urlencode($result['error']));
        }

        // actualizar sesión
        session()->set('user_name', ($result['first_name'] ?? '') . ' ' . ($result['last_name'] ?? ''));
        session()->set('user_email', $result['email'] ?? '');

        return redirect()->to(site_url('profile/edit') . '?msg=' . urlencode('Profile updated successfully'));
    }
}
