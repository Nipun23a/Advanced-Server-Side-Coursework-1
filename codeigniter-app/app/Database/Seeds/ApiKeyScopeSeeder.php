<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ApiKeyScopeSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'developer@example.com', 'client_type' => 'web'],
            ['email' => 'alumni1@example.com',   'client_type' => 'mobile'],
            ['email' => 'alumni2@example.com',   'client_type' => 'third_party'],
        ];

        foreach ($entries as $entry) {
            $user = $this->db->table('users')->where('email', $entry['email'])->get()->getRowArray();
            if (! $user) {
                continue;
            }

            $apiKey = $this->db->table('api_keys')->where('user_id', $user['id'])->get()->getRowArray();
            if (! $apiKey) {
                continue;
            }

            $exists = $this->db->table('api_key_scopes')
                ->where('api_key_id', $apiKey['id'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('api_key_scopes')->insert([
                    'api_key_id'  => $apiKey['id'],
                    'client_type' => $entry['client_type'],
                ]);
            }
        }
    }
}
