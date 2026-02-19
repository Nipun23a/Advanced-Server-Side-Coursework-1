<?php

namespace App\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 100,'unique' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 256],
            'role' => ['type' => 'ENUM', 'constraint' => ['developer','admin','alumni']],
            'is_email_verified'=> ['type' => 'BOOLEAN', 'default' => false],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }

    public function down(): void
    {
        $this->forge->dropTable('users');
    }
}
