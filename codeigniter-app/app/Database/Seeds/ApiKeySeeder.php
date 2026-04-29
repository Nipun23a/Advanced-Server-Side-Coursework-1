<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ApiKeySeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'developer@example.com', 'raw_key' => 'dev-api-key-plain-001'],
            ['email' => 'alumni1@example.com',   'raw_key' => 'alumni1-api-key-plain-002'],
            ['email' => 'alumni2@example.com',   'raw_key' => 'alumni2-api-key-plain-003'],
        ];

        foreach ($entries as $entry) {
            $user = $this->db->table('users')->where('email', $entry['email'])->get()->getRowArray();
            if (! $user) {
                continue;
            }

            $exists = $this->db->table('api_keys')->where('user_id', $user['id'])->countAllResults();
            if (! $exists) {
                $this->db->table('api_keys')->insert([
                    'user_id'  => $user['id'],
                    'key_hash' => hash('sha256', $entry['raw_key']),
                    'is_active' => 1,
                ]);
            }
        }
    }
}
