<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AlumniEventSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'alumni1@example.com', 'event_name' => 'Annual Alumni Networking Night', 'event_date' => '2026-02-15'],
            ['email' => 'alumni1@example.com', 'event_name' => 'Tech Industry Panel 2026',       'event_date' => '2026-03-22'],
            ['email' => 'alumni2@example.com', 'event_name' => 'Annual Alumni Networking Night', 'event_date' => '2026-02-15'],
            ['email' => 'alumni3@example.com', 'event_name' => 'AI & Data Science Summit',       'event_date' => '2026-04-10'],
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

            $exists = $this->db->table('alumni_events')
                ->where('profile_id', $profile['id'])
                ->where('event_name', $entry['event_name'])
                ->where('event_date', $entry['event_date'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('alumni_events')->insert([
                    'profile_id' => $profile['id'],
                    'event_name' => $entry['event_name'],
                    'event_date' => $entry['event_date'],
                ]);
            }
        }
    }
}
