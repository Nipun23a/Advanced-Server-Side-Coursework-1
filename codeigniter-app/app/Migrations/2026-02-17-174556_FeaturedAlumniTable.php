<?php

namespace App\Migrations;

use CodeIgniter\Database\Migration;

class FeaturedAlumniTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'bid_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'featured_at' => ['type' =>'DATETIME'],
            'created_at DATETIME default current_timestamp'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('bid_id','bids','id','CASCADE','CASCADE');
        $this->forge->createTable('featured_alumni');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('featured_alumni','user_id');
        $this->forge->dropForeignKey('featured_alumni','bid_id');
        $this->forge->dropTable('featured_alumni');
    }
}
