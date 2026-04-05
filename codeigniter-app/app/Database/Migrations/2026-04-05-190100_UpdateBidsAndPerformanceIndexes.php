<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateBidsAndPerformanceIndexes extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('bids') && ! $this->db->fieldExists('sponsorship_offer_id', 'bids')) {
            $fields = [
                'sponsorship_offer_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'user_id',
                ],
            ];

            $this->forge->addColumn('bids', $fields);
            $this->db->query(
                'ALTER TABLE bids
                 ADD CONSTRAINT bids_sponsorship_fk
                 FOREIGN KEY (sponsorship_offer_id)
                 REFERENCES sponsorship_offers(id)
                 ON DELETE SET NULL'
            );
        }

        $this->createIndexIfMissing('bids', 'idx_bids_user_date', 'CREATE INDEX idx_bids_user_date ON bids(user_id, bid_date)');
        $this->createIndexIfMissing('featured_alumni', 'idx_featured_date', 'CREATE INDEX idx_featured_date ON featured_alumni(featured_at)');
        $this->createIndexIfMissing(
            'monthly_feature_counts',
            'idx_monthly_user',
            'CREATE INDEX idx_monthly_user ON monthly_feature_counts(user_id, year, month)'
        );
    }

    public function down(): void
    {
        $this->dropIndexIfExists('monthly_feature_counts', 'idx_monthly_user');
        $this->dropIndexIfExists('featured_alumni', 'idx_featured_date');
        $this->dropIndexIfExists('bids', 'idx_bids_user_date');

        if ($this->db->tableExists('bids') && $this->db->fieldExists('sponsorship_offer_id', 'bids')) {
            $this->db->query('ALTER TABLE bids DROP FOREIGN KEY bids_sponsorship_fk');
            $this->forge->dropColumn('bids', 'sponsorship_offer_id');
        }
    }

    protected function createIndexIfMissing(string $table, string $indexName, string $sql): void
    {
        $query = $this->db->query("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        if (empty($query->getResultArray())) {
            $this->db->query($sql);
        }
    }

    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        $query = $this->db->query("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        if (! empty($query->getResultArray())) {
            $this->db->query("DROP INDEX {$indexName} ON {$table}");
        }
    }
}
