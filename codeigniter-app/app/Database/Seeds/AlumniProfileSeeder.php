<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AlumniProfileSeeder extends Seeder
{
    public function run()
    {
        $existing = $this->db->table('alumni_profiles')
            ->where('user_id', 3)
            ->get()
            ->getRowArray();

        $data = [
            'user_id' => 3,
            'bio' => 'Sample alumni profile for seeded user 3.',
            'linkedin_url' => 'https://www.linkedin.com/in/alumni-user-3',
            'profile_image_url' => 'https://images.example.com/alumni-user-3.jpg',
        ];

        if ($existing) {
            $this->db->table('alumni_profiles')
                ->where('id', $existing['id'])
                ->update($data);

            return;
        }

        $this->db->table('alumni_profiles')->insert($data);
    }
}
