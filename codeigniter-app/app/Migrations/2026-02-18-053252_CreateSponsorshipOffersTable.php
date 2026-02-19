<?php

namespace App\Migrations;

use CodeIgniter\Database\Migration;

class CreateSponsorshipOffersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'sponsorship_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'alumni_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'sponsorable_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'sponsorable_type' => ['type' =>'ENUM','constraint' => ['certificate','license','professional_course']],
            'offer_amount' => ['type' =>'DECIMAL','constraint' => '10,2'],
            'status' => ['type' =>'ENUM','constraint' => ['pending','accepted','declined','paid'],'default' => 'pending'],
            'is_paid' => ['type' =>'BOOLEAN','default' => false],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('sponsorship_id','sponsors','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('alumni_id','alumni_profiles','id','CASCADE','CASCADE');
        $this->forge->createTable('sponsorship_offers');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('sponsorship_offers','sponsorship_id');
        $this->forge->dropForeignKey('sponsorship_offers','alumni_id');
        $this->forge->dropTable('sponsorship_offers');
    }
}
