<?php

namespace App\Models;

use CodeIgniter\Model;

class AlumniProfileModel extends Model
{
    protected $table = 'alumni_profiles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'bio',
        'linkedin_url',
        'profile_image_url',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }
}
