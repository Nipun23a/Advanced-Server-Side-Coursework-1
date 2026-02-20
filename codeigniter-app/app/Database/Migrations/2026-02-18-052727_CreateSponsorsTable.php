<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSponsorsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'sponsor_name' => ['type' =>'VARCHAR','constraint' => 100],
            'sponsor_type' => ['type' => 'ENUM', 'constraint' => ['course_provider', 'licensing_body', 'certification_body'], 'default' => 'course_provider'],
            'website_url' => ['type' =>'VARCHAR','constraint' => 256],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('sponsors');
    }

    public function down(): void
    {
        $this->forge->dropTable('sponsors');
    }
}
