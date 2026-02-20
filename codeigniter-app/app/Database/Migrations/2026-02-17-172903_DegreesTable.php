<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DegreesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'profile_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'degree_name' => ['type' =>'VARCHAR','constraint' => 100],
            'institution_url' => ['type' =>'VARCHAR','constraint' => 256],
            'completion_date' => ['type' =>'DATE'],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('profile_id','alumni_profiles','id','CASCADE','CASCADE');
        $this->forge->createTable('degrees');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('degrees','profile_id');
        $this->forge->dropTable('degrees');
    }
}
