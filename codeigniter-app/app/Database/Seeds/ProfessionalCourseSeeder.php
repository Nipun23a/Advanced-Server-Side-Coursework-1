<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProfessionalCourseSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'alumni1@example.com', 'course_name' => 'AWS Solutions Architect',   'provider_url' => 'https://www.aws.amazon.com/training', 'completion_date' => '2021-03-10'],
            ['email' => 'alumni1@example.com', 'course_name' => 'Docker & Kubernetes',        'provider_url' => 'https://www.udemy.com',                'completion_date' => '2022-07-25'],
            ['email' => 'alumni2@example.com', 'course_name' => 'Product Management Basics',  'provider_url' => 'https://www.coursera.org',             'completion_date' => '2020-11-05'],
            ['email' => 'alumni3@example.com', 'course_name' => 'Deep Learning Specialisation','provider_url' => 'https://www.coursera.org',             'completion_date' => '2023-01-20'],
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

            $exists = $this->db->table('professional_courses')
                ->where('profile_id', $profile['id'])
                ->where('course_name', $entry['course_name'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('professional_courses')->insert([
                    'profile_id'      => $profile['id'],
                    'course_name'     => $entry['course_name'],
                    'provider_url'    => $entry['provider_url'],
                    'completion_date' => $entry['completion_date'],
                ]);
            }
        }
    }
}
