<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DegreeSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'alumni1@example.com', 'degree_name' => 'BSc Computer Science',        'institution_url' => 'https://www.mit.edu',       'completion_date' => '2016-06-15'],
            ['email' => 'alumni1@example.com', 'degree_name' => 'MSc Software Engineering',    'institution_url' => 'https://www.stanford.edu',  'completion_date' => '2018-05-20'],
            ['email' => 'alumni2@example.com', 'degree_name' => 'BBA Business Administration', 'institution_url' => 'https://www.harvard.edu',   'completion_date' => '2015-07-01'],
            ['email' => 'alumni3@example.com', 'degree_name' => 'BSc Mathematics',             'institution_url' => 'https://www.cam.ac.uk',     'completion_date' => '2017-06-30'],
            ['email' => 'alumni3@example.com', 'degree_name' => 'PhD Machine Learning',        'institution_url' => 'https://www.ox.ac.uk',      'completion_date' => '2022-09-01'],
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

            $exists = $this->db->table('degrees')
                ->where('profile_id', $profile['id'])
                ->where('degree_name', $entry['degree_name'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('degrees')->insert([
                    'profile_id'      => $profile['id'],
                    'degree_name'     => $entry['degree_name'],
                    'institution_url' => $entry['institution_url'],
                    'completion_date' => $entry['completion_date'],
                ]);
            }
        }
    }
}
