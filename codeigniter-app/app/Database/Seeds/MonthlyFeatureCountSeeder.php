<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MonthlyFeatureCountSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'alumni1@example.com', 'attended_event' => 1, 'year' => 2026, 'month' => 1, 'count' => 3],
            ['email' => 'alumni1@example.com', 'attended_event' => 1, 'year' => 2026, 'month' => 2, 'count' => 2],
            ['email' => 'alumni2@example.com', 'attended_event' => 1, 'year' => 2026, 'month' => 1, 'count' => 1],
            ['email' => 'alumni3@example.com', 'attended_event' => 0, 'year' => 2026, 'month' => 3, 'count' => 4],
        ];

        foreach ($entries as $entry) {
            $user = $this->db->table('users')->where('email', $entry['email'])->get()->getRowArray();
            if (! $user) {
                continue;
            }

            $exists = $this->db->table('monthly_feature_counts')
                ->where('user_id', $user['id'])
                ->where('year', $entry['year'])
                ->where('month', $entry['month'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('monthly_feature_counts')->insert([
                    'user_id'        => $user['id'],
                    'attended_event' => $entry['attended_event'],
                    'year'           => $entry['year'],
                    'month'          => $entry['month'],
                    'count'          => $entry['count'],
                ]);
            }
        }
    }
}
