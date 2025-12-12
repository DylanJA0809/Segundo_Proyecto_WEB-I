<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'role','status',
        'first_name','last_name',
        'national_id','birth_date',
        'email','phone',
        'bio',
        'photo_path',
        'password_hash',
        'created_at','updated_at'
    ];

    public function getBioById(int $userId): ?array
    {
        return $this->select('first_name, bio')
            ->where('id', $userId)
            ->first();
    }

    public function updateBioById(int $userId, string $bio): array
    {
        if ($userId <= 0) return ['ok' => false, 'error' => 'Invalid session'];

        $ok = $this->where('id', $userId)
            ->set([
                'bio' => $bio,
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();

        if (!$ok) return ['ok' => false, 'error' => 'Update failed'];
        return ['ok' => true];
    }

    public function updateProfile(int $userId, array $input, $photoFile = null): array
    {
        $first_name  = trim($input['first_name'] ?? '');
        $last_name   = trim($input['last_name'] ?? '');
        $national_id = trim($input['national_id'] ?? '');
        $birth_date  = trim($input['birth_date'] ?? '');
        $email       = trim($input['email'] ?? '');
        $phone       = trim($input['phone'] ?? '');
        $pass        = (string)($input['password'] ?? '');
        $pass2       = (string)($input['password2'] ?? '');
        $currentPhoto= trim($input['current_photo'] ?? '');

        if ($userId <= 0 || $first_name==='' || $last_name==='' || $national_id==='' || $birth_date==='' || $email==='' || $phone==='') {
            return ['ok' => false, 'error' => 'Missing required fields'];
        }

        // Email único
        $exists = $this->select('id')
            ->where('LOWER(email)', strtolower($email))
            ->where('id !=', $userId)
            ->first();

        if ($exists) {
            return ['ok' => false, 'error' => 'Email already in use'];
        }

        // Foto
        $photo_path = $currentPhoto ?: null;

        if ($photoFile && $photoFile->isValid() && !$photoFile->hasMoved()) {
            $mime = $photoFile->getMimeType();
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ];

            if (!isset($allowed[$mime])) {
                return ['ok' => false, 'error' => 'Invalid image type. Use JPG/PNG/WEBP'];
            }

            if ($photoFile->getSize() > 2 * 1024 * 1024) {
                return ['ok' => false, 'error' => 'Image too large (max 2MB)'];
            }

            $ext = $allowed[$mime];
            $safeName = 'u'.$userId.'_'.time().'.'.$ext;

            // Guardar en /public/Img/users
            $destDir = FCPATH . 'Img/users';
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0777, true);
            }

            if (!$photoFile->move($destDir, $safeName)) {
                return ['ok' => false, 'error' => 'Cannot save image'];
            }

            $photo_path = '/Img/users/' . $safeName;
        }

        // Password
        $data = [
            'first_name'  => $first_name,
            'last_name'   => $last_name,
            'national_id' => $national_id,
            'birth_date'  => $birth_date,
            'email'       => $email,
            'phone'       => $phone,
            'photo_path'  => $photo_path,
            'updated_at'  => date('Y-m-d H:i:s'),
        ];

        if ($pass !== '' || $pass2 !== '') {
            if ($pass !== $pass2) return ['ok' => false, 'error' => 'Passwords do not match'];
            if (strlen($pass) < 6) return ['ok' => false, 'error' => 'Password must be at least 6 characters'];
            $data['password_hash'] = password_hash($pass, PASSWORD_DEFAULT);
        }

        $ok = $this->update($userId, $data);
        if (!$ok) return ['ok' => false, 'error' => 'DB error: update failed'];

        return ['ok' => true, 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email];
    }
}
