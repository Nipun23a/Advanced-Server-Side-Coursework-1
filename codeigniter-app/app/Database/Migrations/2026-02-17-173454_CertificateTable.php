<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CertificateTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'profile_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'certificate_name' => ['type' =>'VARCHAR','constraint' => 100],
            'issuer_name' => ['type' =>'VARCHAR','constraint' => 256],
            'completion_date' => ['type' =>'DATE'],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp'
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('profile_id','alumni_profiles','id','CASCADE','CASCADE');
        $this->forge->createTable('certificates');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('certificates','profile_id');
        $this->forge->dropTable('certificates');
    }
}
