<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BidSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['email' => 'alumni1@example.com', 'bid_amount' => 150.00, 'bid_status' => 'won',    'bid_date' => '2026-01-10 09:00:00', 'is_cancelled' => 0],
            ['email' => 'alumni1@example.com', 'bid_amount' => 200.00, 'bid_status' => 'active',  'bid_date' => '2026-03-05 11:30:00', 'is_cancelled' => 0],
            ['email' => 'alumni2@example.com', 'bid_amount' => 175.00, 'bid_status' => 'won',    'bid_date' => '2026-01-15 14:00:00', 'is_cancelled' => 0],
            ['email' => 'alumni2@example.com', 'bid_amount' => 120.00, 'bid_status' => 'lost',   'bid_date' => '2026-02-20 10:00:00', 'is_cancelled' => 0],
            ['email' => 'alumni3@example.com', 'bid_amount' => 300.00, 'bid_status' => 'active',  'bid_date' => '2026-04-01 08:45:00', 'is_cancelled' => 0],
        ];

        foreach ($entries as $entry) {
            $user = $this->db->table('users')->where('email', $entry['email'])->get()->getRowArray();
            if (! $user) {
                continue;
            }

            // Only seed if this user has no bids at all yet
            $exists = $this->db->table('bids')
                ->where('user_id', $user['id'])
                ->where('bid_amount', $entry['bid_amount'])
                ->where('bid_date', $entry['bid_date'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('bids')->insert([
                    'user_id'              => $user['id'],
                    'bid_amount'           => $entry['bid_amount'],
                    'bid_status'           => $entry['bid_status'],
                    'bid_date'             => $entry['bid_date'],
                    'is_cancelled'         => $entry['is_cancelled'],
                    'sponsorship_offer_id' => null,
                ]);
            }
        }
    }
}
