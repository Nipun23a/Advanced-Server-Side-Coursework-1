<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SponsorshipOfferSeeder extends Seeder
{
    public function run()
    {
        // Each entry: which sponsor, which alumni, which sponsorable type, and a lookup
        // field to find the sponsorable_id from the relevant table.
        $entries = [
            [
                'sponsor_name'     => 'CloudPath Academy',
                'alumni_email'     => 'alumni1@example.com',
                'sponsorable_type' => 'professional_course',
                'sponsorable_name' => 'AWS Solutions Architect',
                'offer_amount'     => 500.00,
                'status'           => 'accepted',
                'is_paid'          => 1,
            ],
            [
                'sponsor_name'     => 'Professional Boards',
                'alumni_email'     => 'alumni1@example.com',
                'sponsorable_type' => 'license',
                'sponsorable_name' => 'Certified Kubernetes Administrator',
                'offer_amount'     => 300.00,
                'status'           => 'pending',
                'is_paid'          => 0,
            ],
            [
                'sponsor_name'     => 'CertifyGlobal',
                'alumni_email'     => 'alumni2@example.com',
                'sponsorable_type' => 'certificate',
                'sponsorable_name' => 'Certified Scrum Master',
                'offer_amount'     => 250.00,
                'status'           => 'accepted',
                'is_paid'          => 0,
            ],
        ];

        foreach ($entries as $entry) {
            $sponsor = $this->db->table('sponsors')->where('sponsor_name', $entry['sponsor_name'])->get()->getRowArray();
            if (! $sponsor) {
                continue;
            }

            $user = $this->db->table('users')->where('email', $entry['alumni_email'])->get()->getRowArray();
            if (! $user) {
                continue;
            }

            $profile = $this->db->table('alumni_profiles')->where('user_id', $user['id'])->get()->getRowArray();
            if (! $profile) {
                continue;
            }

            // Resolve sponsorable_id from the relevant table
            $sponsorableId = $this->resolveSponsorableId($entry['sponsorable_type'], $entry['sponsorable_name'], $profile['id']);
            if (! $sponsorableId) {
                continue;
            }

            $exists = $this->db->table('sponsorship_offers')
                ->where('sponsorship_id', $sponsor['id'])
                ->where('alumni_id', $profile['id'])
                ->where('sponsorable_type', $entry['sponsorable_type'])
                ->where('sponsorable_id', $sponsorableId)
                ->countAllResults();

            if (! $exists) {
                $this->db->table('sponsorship_offers')->insert([
                    'sponsorship_id'   => $sponsor['id'],
                    'alumni_id'        => $profile['id'],
                    'sponsorable_id'   => $sponsorableId,
                    'sponsorable_type' => $entry['sponsorable_type'],
                    'offer_amount'     => $entry['offer_amount'],
                    'remaining_amount' => $entry['is_paid'] ? 0.00 : $entry['offer_amount'],
                    'status'           => $entry['status'],
                    'is_paid'          => $entry['is_paid'],
                ]);
            }
        }
    }

    private function resolveSponsorableId(string $type, string $name, int $profileId): ?int
    {
        switch ($type) {
            case 'professional_course':
                $row = $this->db->table('professional_courses')
                    ->where('profile_id', $profileId)
                    ->where('course_name', $name)
                    ->get()->getRowArray();
                return $row ? (int) $row['id'] : null;

            case 'license':
                $row = $this->db->table('licenses')
                    ->where('profile_id', $profileId)
                    ->where('license_name', $name)
                    ->get()->getRowArray();
                return $row ? (int) $row['id'] : null;

            case 'certificate':
                $row = $this->db->table('certificates')
                    ->where('profile_id', $profileId)
                    ->where('certificate_name', $name)
                    ->get()->getRowArray();
                return $row ? (int) $row['id'] : null;
        }

        return null;
    }
}
