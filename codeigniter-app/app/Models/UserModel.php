<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = ['email', 'password_hash','is_email_verified','role'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $validationRules    = [
        'email' => 'required|valid_email|is_unique[users.email]',
        'password_hash' => 'required|min_length[8]'
    ];

    protected $validationMessages = [
        'email' => [
            'required' => 'Email Address is required',
            'valid_email' => 'Please enter a valid email address',
            'is_unique' => 'This email address is already registered'
        ],
    ];


    /*
    * Check user exists by email
    */
    public function findByEmail (string $email) : ? array
    {
        return $this -> where ('email', $email) -> first ();
    }

    /*
     *  Check if email exists
     */
    public function emailExists (string $email) : bool
    {
        return $this -> where('email', $email) -> countAllResults() > 0;
    }

    /*
     *  Mark email as verified
     */
    public function markEmailVerified(int $userId) : bool
    {
        return $this->update($userId, ['is_email_verified' => true]);
    }

    /*
     * For Update Password
     */
    public function updatePassword (int $userId, string $newPasswordHash) : bool
    {
        return $this-> update($userId, ['password_hash' => $newPasswordHash]);
    }

}
