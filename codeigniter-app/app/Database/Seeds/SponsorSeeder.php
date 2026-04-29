<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SponsorSeeder extends Seeder
{
    public function run()
    {
        $sponsors = [
            ['sponsor_name' => 'CloudPath Academy',   'sponsor_type' => 'course_provider',    'website_url' => 'https://www.cloudpathacademy.com'],
            ['sponsor_name' => 'Professional Boards',  'sponsor_type' => 'licensing_body',     'website_url' => 'https://www.profboards.org'],
            ['sponsor_name' => 'CertifyGlobal',        'sponsor_type' => 'certification_body', 'website_url' => 'https://www.certifyglobal.com'],
        ];

        foreach ($sponsors as $sponsor) {
            $exists = $this->db->table('sponsors')
                ->where('sponsor_name', $sponsor['sponsor_name'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('sponsors')->insert($sponsor);
            }
        }
    }
}
