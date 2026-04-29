<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EmploymentHistorySeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'alumni1@example.com', 'company_name' => 'TechCorp Ltd',      'industry_sector' => 'Information Technology', 'job_title' => 'Junior Developer',   'start_date' => '2016-09-01', 'end_date' => '2018-08-31'],
            ['email' => 'alumni1@example.com', 'company_name' => 'FinTech Solutions',  'industry_sector' => 'Financial Services',     'job_title' => 'Senior Developer',    'start_date' => '2018-09-01', 'end_date' => '2023-12-31'],
            ['email' => 'alumni2@example.com', 'company_name' => 'SaaSify Inc',        'industry_sector' => 'Software as a Service',  'job_title' => 'Product Manager',     'start_date' => '2015-07-01', 'end_date' => '2020-06-30'],
            ['email' => 'alumni2@example.com', 'company_name' => 'GrowthBase',         'industry_sector' => 'Marketing Technology',   'job_title' => 'Senior PM',           'start_date' => '2020-07-01', 'end_date' => '2024-03-31'],
            ['email' => 'alumni3@example.com', 'company_name' => 'DataLab Research',   'industry_sector' => 'Research & Development', 'job_title' => 'ML Research Engineer','start_date' => '2022-10-01', 'end_date' => '2025-01-31'],
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

            $exists = $this->db->table('employment_history')
                ->where('profile_id', $profile['id'])
                ->where('company_name', $entry['company_name'])
                ->where('job_title', $entry['job_title'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('employment_history')->insert([
                    'profile_id'      => $profile['id'],
                    'company_name'    => $entry['company_name'],
                    'industry_sector' => $entry['industry_sector'],
                    'job_title'       => $entry['job_title'],
                    'start_date'      => $entry['start_date'],
                    'end_date'        => $entry['end_date'],
                ]);
            }
        }
    }
}
