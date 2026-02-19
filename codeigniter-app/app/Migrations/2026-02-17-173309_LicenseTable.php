<?php

namespace App\Migrations;

use CodeIgniter\Database\Migration;

class LicenseTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'profile_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'license_name' => ['type' =>'VARCHAR','constraint' => 100],
            'license_url' => ['type' =>'VARCHAR','constraint' => 256],
            'completion_date' => ['type' =>'DATE'],
            'expiration_date' => ['type' =>'DATE'],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('profile_id','alumni_profiles','id','CASCADE','CASCADE');
        $this->forge->createTable('licenses');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('licenses','profile_id');
        $this->forge->dropTable('licenses');
    }
}
