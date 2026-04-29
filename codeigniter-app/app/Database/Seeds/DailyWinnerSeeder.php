<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DailyWinnerSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'alumni1@example.com', 'bid_amount' => 150.00, 'bid_date' => '2026-01-10 09:00:00', 'winner_date' => '2026-01-11'],
            ['email' => 'alumni2@example.com', 'bid_amount' => 175.00, 'bid_date' => '2026-01-15 14:00:00', 'winner_date' => '2026-01-16'],
        ];

        foreach ($entries as $entry) {
            $user = $this->db->table('users')->where('email', $entry['email'])->get()->getRowArray();
            if (! $user) {
                continue;
            }

            $bid = $this->db->table('bids')
                ->where('user_id', $user['id'])
                ->where('bid_amount', $entry['bid_amount'])
                ->where('bid_date', $entry['bid_date'])
                ->get()->getRowArray();

            if (! $bid) {
                continue;
            }

            $exists = $this->db->table('daily_winners')
                ->where('bid_id', $bid['id'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('daily_winners')->insert([
                    'user_id'     => $user['id'],
                    'bid_id'      => $bid['id'],
                    'winner_date' => $entry['winner_date'],
                ]);
            }
        }
    }
}
