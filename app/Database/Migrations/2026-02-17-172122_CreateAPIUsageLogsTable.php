<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAPIUsageLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'api_key_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'endpoint' => ['type' =>'VARCHAR','constraint' => 256],
            'http_method' => ['type' =>'VARCHAR','constraint' => 256],
            'source_ip' => ['type' =>'VARCHAR','constraint' => 256],
            'access_at' => ['type' =>'DATETIME','default'=>null],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('api_key_id','api_keys','id','CASCADE','CASCADE');
        $this->forge->createTable('api_usage_logs');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('api_usage_logs','api_key_id');
        $this->forge->dropTable('api_usage_logs');
    }
}
