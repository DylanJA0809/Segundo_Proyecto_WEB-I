<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\RawSql;


class LoginTokenModel extends Model
{
    protected $table      = 'login_tokens';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',
        'token',
        'expires_at',
        'used_at',
        'created_at',
    ];

    public $useTimestamps = false;
}
