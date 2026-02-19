<?php

namespace App\Migrations;

use CodeIgniter\Database\Migration;

class BidsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'bid_amount' => ['type' =>'DECIMAL','constraint' => '10,2'],
            'bid_status' => ['type' =>'ENUM("active","won","lost")','default' => 'active'],
            'bid_date' => ['type' =>'DATETIME'],
            'is_cancelled' => ['type' =>'BOOLEAN','default' => false],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->createTable('bids');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('bids','user_id');
        $this->forge->dropTable('bids');
    }
}
