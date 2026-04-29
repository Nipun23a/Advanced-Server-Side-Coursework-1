<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CertificateSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'alumni1@example.com', 'certificate_name' => 'AWS Certified Developer',       'issuer_name' => 'Amazon Web Services', 'completion_date' => '2021-06-01'],
            ['email' => 'alumni1@example.com', 'certificate_name' => 'Google Cloud Associate',        'issuer_name' => 'Google Cloud',        'completion_date' => '2023-02-14'],
            ['email' => 'alumni2@example.com', 'certificate_name' => 'Certified Scrum Master',        'issuer_name' => 'Scrum Alliance',      'completion_date' => '2020-03-18'],
            ['email' => 'alumni3@example.com', 'certificate_name' => 'IBM Data Science Professional', 'issuer_name' => 'IBM',                 'completion_date' => '2022-11-30'],
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

            $exists = $this->db->table('certificates')
                ->where('profile_id', $profile['id'])
                ->where('certificate_name', $entry['certificate_name'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('certificates')->insert([
                    'profile_id'       => $profile['id'],
                    'certificate_name' => $entry['certificate_name'],
                    'issuer_name'      => $entry['issuer_name'],
                    'completion_date'  => $entry['completion_date'],
                ]);
            }
        }
    }
}
