<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDailyWinnersAndAlumniEvents extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('daily_winners')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 10,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                ],
                'bid_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                ],
                'winner_date' => [
                    'type' => 'DATE',
                ],
                'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('winner_date');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('bid_id', 'bids', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('daily_winners', true);
        }

        if (! $this->db->tableExists('alumni_events')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 10,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'profile_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                ],
                'event_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'event_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey(['profile_id', 'event_date']);
            $this->forge->addForeignKey('profile_id', 'alumni_profiles', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('alumni_events', true);
        }

        if ($this->db->tableExists('featured_alumni')) {
            $this->db->query(
                'INSERT INTO daily_winners (user_id, bid_id, winner_date, created_at)
                 SELECT fa.user_id, fa.bid_id, DATE(fa.featured_at), COALESCE(fa.created_at, NOW())
                 FROM featured_alumni fa
                 WHERE NOT EXISTS (
                     SELECT 1
                     FROM daily_winners dw
                     WHERE dw.bid_id = fa.bid_id
                 )'
            );
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('alumni_events')) {
            $this->forge->dropTable('alumni_events', true);
        }

        if ($this->db->tableExists('daily_winners')) {
            $this->forge->dropTable('daily_winners', true);
        }
    }
}
