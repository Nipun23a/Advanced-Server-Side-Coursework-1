<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfessionalCourseModel extends Model
{
    protected $table = 'professional_courses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['profile_id', 'course_name', 'provider_url', 'completion_date'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
