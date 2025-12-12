<?php

namespace App\Models;

use CodeIgniter\Model;

class SearchLogModel extends Model
{
    protected $table      = 'search_logs';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id',
        'departure',
        'arrival',
        'results_count',
        'created_at',
    ];

    public $useTimestamps = false;
}
