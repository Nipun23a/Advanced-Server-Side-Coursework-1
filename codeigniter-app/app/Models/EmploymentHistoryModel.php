<?php

namespace App\Models;

use CodeIgniter\Model;

class EmploymentHistoryModel extends Model
{
    protected $table = 'employment_history';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['profile_id', 'company_name', 'job_title', 'start_date', 'end_date'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
