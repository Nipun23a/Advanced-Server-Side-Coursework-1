<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'email' => 'developer@example.com',
                'password_hash' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'developer',
                'is_email_verified' => 1,
            ],
            [
                'email' => 'admin@example.com',
                'password_hash' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_email_verified' => 1,
            ],
            [
                'email' => 'alumni@example.com',
                'password_hash' => password_hash('123456', PASSWORD_DEFAULT),
                'role' => 'alumni',
                'is_email_verified' => 1,
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
