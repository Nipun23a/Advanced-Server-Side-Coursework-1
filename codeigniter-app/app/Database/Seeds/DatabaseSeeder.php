<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Main seeder – run with:
 *   php spark db:seed DatabaseSeeder
 *
 * Seeders run in dependency order so FK constraints are never violated.
 * Every individual seeder is idempotent (safe to re-run).
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ── Tier 1: no foreign-key dependencies ─────────────────────────────
        $this->call('UserSeeder');
        $this->call('InternalServiceSecretSeeder');
        $this->call('SponsorSeeder');

        // ── Tier 2: depend on users ──────────────────────────────────────────
        $this->call('AlumniProfileSeeder');
        $this->call('ApiKeySeeder');

        // ── Tier 3: depend on api_keys ───────────────────────────────────────
        $this->call('ApiKeyPermissionSeeder');
        $this->call('ApiKeyScopeSeeder');
        $this->call('ApiUsageLogSeeder');

        // ── Tier 4: depend on alumni_profiles ────────────────────────────────
        $this->call('DegreeSeeder');
        $this->call('ProfessionalCourseSeeder');
        $this->call('EmploymentHistorySeeder');
        $this->call('LicenseSeeder');
        $this->call('CertificateSeeder');
        $this->call('AlumniEventSeeder');

        // ── Tier 5: depend on users (bids – sponsorship_offer_id is nullable) ─
        $this->call('BidSeeder');

        // ── Tier 6: depend on sponsors + alumni_profiles + profile sub-tables ─
        $this->call('SponsorshipOfferSeeder');

        // ── Tier 7: depend on users + bids ───────────────────────────────────
        $this->call('MonthlyFeatureCountSeeder');
        $this->call('FeaturedAlumniSeeder');
        $this->call('DailyWinnerSeeder');
    }
}
