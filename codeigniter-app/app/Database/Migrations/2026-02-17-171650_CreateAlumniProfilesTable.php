<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAlumniProfilesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'bio' => ['type' =>'TEXT'],
            'linkedin_url' => ['type' =>'VARCHAR','constraint' => 256],
            'profile_image_url' => ['type' =>'VARCHAR','constraint' => 256],
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->createTable('alumni_profiles');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('alumni_profiles','user_id');
        $this->forge->dropTable('alumni_profiles');
    }
}
