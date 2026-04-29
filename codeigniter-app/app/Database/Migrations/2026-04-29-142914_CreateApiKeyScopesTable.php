<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiKeyScopesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'api_key_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'client_type' => ['type' =>'VARCHAR','constraint' => 50, 'default' => 'third_party'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('api_key_id');
        $this->forge->addForeignKey('api_key_id','api_keys','id','CASCADE','CASCADE');
        $this->forge->createTable('api_key_scopes');
    }

    public function down()
    {
        $this->forge->dropForeignKey('api_key_scopes', 'api_key_scopes_api_key_id_foreign');
        $this->forge->dropTable('api_key_scopes');
    }
}
