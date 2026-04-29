<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LicenseSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'alumni1@example.com', 'license_name' => 'Certified Kubernetes Administrator', 'license_url' => 'https://www.cncf.io/certification/cka/',   'completion_date' => '2022-04-15', 'expiration_date' => '2025-04-15'],
            ['email' => 'alumni2@example.com', 'license_name' => 'PMP Certification',                   'license_url' => 'https://www.pmi.org/certifications/pmp',   'completion_date' => '2021-08-20', 'expiration_date' => '2024-08-20'],
            ['email' => 'alumni3@example.com', 'license_name' => 'TensorFlow Developer Certificate',    'license_url' => 'https://www.tensorflow.org/certificate',   'completion_date' => '2023-03-10', 'expiration_date' => '2026-03-10'],
        ];

        foreach ($entries as $entry) {
            $user = $this->db->table('users')->where('email', $entry['email'])->get()->getRowArray();
            if (! $user) {
                continue;
            }

            $profile = $this->db->table('alumni_profiles')->where('user_id', $user['id'])->get()->getRowArray();
            if (! $profile) {
                continue;
            }

            $exists = $this->db->table('licenses')
                ->where('profile_id', $profile['id'])
                ->where('license_name', $entry['license_name'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('licenses')->insert([
                    'profile_id'      => $profile['id'],
                    'license_name'    => $entry['license_name'],
                    'license_url'     => $entry['license_url'],
                    'completion_date' => $entry['completion_date'],
                    'expiration_date' => $entry['expiration_date'],
                ]);
            }
        }
    }
}
