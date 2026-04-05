<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenseModel extends Model
{
    protected $table = 'licenses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['profile_id', 'license_name', 'license_url', 'completion_date', 'expiration_date'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
