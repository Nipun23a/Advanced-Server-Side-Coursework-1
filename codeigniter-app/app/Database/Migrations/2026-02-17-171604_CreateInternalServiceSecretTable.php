<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInternalServiceSecretTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'service_name' => ['type' =>'VARCHAR','constraint' => 256],
            'secret_hash' => ['type' =>'VARCHAR','constraint' => 256],
            'is_active' => ['type' =>'BOOLEAN','default' => true],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('internal_service_secrets');
    }

    public function down(): void
    {
        $this->forge->dropTable('internal_service_secrets');
    }
}
