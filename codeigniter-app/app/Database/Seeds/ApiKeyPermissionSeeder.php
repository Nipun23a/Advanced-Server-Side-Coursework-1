<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ApiKeyPermissionSeeder extends Seeder
{
    public function run()
    {
        // Map email → permissions to grant
        $entries = [
            'developer@example.com' => ['read:alumni_profiles', 'write:alumni_profiles', 'read:bids', 'write:bids'],
            'alumni1@example.com'   => ['read:alumni_profiles', 'read:bids'],
            'alumni2@example.com'   => ['read:alumni_profiles'],
        ];

        foreach ($entries as $email => $permissions) {
            $user = $this->db->table('users')->where('email', $email)->get()->getRowArray();
            if (! $user) {
                continue;
            }

            $apiKey = $this->db->table('api_keys')->where('user_id', $user['id'])->get()->getRowArray();
            if (! $apiKey) {
                continue;
            }

            foreach ($permissions as $permission) {
                $exists = $this->db->table('api_key_permissions')
                    ->where('api_key_id', $apiKey['id'])
                    ->where('permission', $permission)
                    ->countAllResults();

                if (! $exists) {
                    $this->db->table('api_key_permissions')->insert([
                        'api_key_id' => $apiKey['id'],
                        'permission' => $permission,
                    ]);
                }
            }
        }
    }
}
