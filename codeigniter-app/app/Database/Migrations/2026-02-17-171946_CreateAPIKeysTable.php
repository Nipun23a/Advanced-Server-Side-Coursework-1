<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAPIKeysTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'key_hash' => ['type' =>'VARCHAR','constraint' => 256],
            'created_at DATETIME default current_timestamp',
            'revoked_at DATETIME default null',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->createTable('api_keys');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('api_keys','user_id');
        $this->forge->dropTable('api_keys');
    }
}
