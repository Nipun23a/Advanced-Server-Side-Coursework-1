<?php

namespace App\Migrations;

use CodeIgniter\Database\Migration;

class MonthlyFeatureCountTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'attended_event' => ['type' =>'BOOLEAN','default' => true],
            'year' => ['type' =>'INT','constraint' => 4],
            'month' => ['type' =>'INT','constraint' => 2],
            'count' => ['type' =>'INT','constraint' => 10],
            'created_at DATETIME default current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->createTable('monthly_feature_counts');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('monthly_feature_counts','user_id');
        $this->forge->dropTable('monthly_feature_counts');
    }
}
