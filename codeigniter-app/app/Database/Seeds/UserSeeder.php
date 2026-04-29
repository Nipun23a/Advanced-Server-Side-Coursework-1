<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['email' => 'developer@example.com', 'role' => 'developer'],
            ['email' => 'admin@example.com',     'role' => 'admin'],
            ['email' => 'alumni1@example.com',   'role' => 'alumni'],
            ['email' => 'alumni2@example.com',   'role' => 'alumni'],
            ['email' => 'alumni3@example.com',   'role' => 'alumni'],
        ];

        foreach ($users as $user) {
            $exists = $this->db->table('users')->where('email', $user['email'])->countAllResults();
            if (! $exists) {
                $this->db->table('users')->insert([
                    'email'             => $user['email'],
                    'password_hash'     => password_hash('password123', PASSWORD_DEFAULT),
                    'role'              => $user['role'],
                    'is_email_verified' => 1,
                ]);
            }
        }
    }
}
