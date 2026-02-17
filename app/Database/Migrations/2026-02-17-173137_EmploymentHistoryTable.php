<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EmploymentHistoryTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'profile_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'company_name' => ['type' =>'VARCHAR','constraint' => 100],
            'job_title' => ['type' =>'VARCHAR','constraint' => 100],
            'start_date' => ['type' =>'DATE'],
            'end_date' => ['type' =>'DATE'],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('profile_id','alumni_profiles','id','CASCADE','CASCADE');
        $this->forge->createTable('employment_history');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('employment_history','profile_id');
        $this->forge->dropTable('employment_history');
    }
}
