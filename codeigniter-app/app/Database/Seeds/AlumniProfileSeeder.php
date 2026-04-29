<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AlumniProfileSeeder extends Seeder
{
    public function run()
    {
        $profiles = [
            [
                'email'             => 'alumni1@example.com',
                'bio'               => 'Software engineer with 8 years of experience in fintech.',
                'linkedin_url'      => 'https://www.linkedin.com/in/alumni-one',
                'profile_image_url' => 'https://images.example.com/alumni1.jpg',
            ],
            [
                'email'             => 'alumni2@example.com',
                'bio'               => 'Product manager specialising in SaaS platforms.',
                'linkedin_url'      => 'https://www.linkedin.com/in/alumni-two',
                'profile_image_url' => 'https://images.example.com/alumni2.jpg',
            ],
            [
                'email'             => 'alumni3@example.com',
                'bio'               => 'Data scientist with a background in machine learning research.',
                'linkedin_url'      => 'https://www.linkedin.com/in/alumni-three',
                'profile_image_url' => 'https://images.example.com/alumni3.jpg',
            ],
        ];

        foreach ($profiles as $profile) {
            $user = $this->db->table('users')->where('email', $profile['email'])->get()->getRowArray();
            if (! $user) {
                continue;
            }

            $exists = $this->db->table('alumni_profiles')->where('user_id', $user['id'])->get()->getRowArray();
            $data   = [
                'user_id'           => $user['id'],
                'bio'               => $profile['bio'],
                'linkedin_url'      => $profile['linkedin_url'],
                'profile_image_url' => $profile['profile_image_url'],
            ];

            if ($exists) {
                $this->db->table('alumni_profiles')->where('id', $exists['id'])->update($data);
            } else {
                $this->db->table('alumni_profiles')->insert($data);
            }
        }
    }
}
