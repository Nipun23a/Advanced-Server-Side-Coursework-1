<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmailVerificationTokenTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' =>'INT','constraint' => 10, 'unsigned' => true],
            'token_hash' => ['type' =>'VARCHAR','constraint' => 256],
            'expires_at' => ['type' =>'DATETIME'],
            'used_at DATETIME default null',
            'created_at DATETIME default current_timestamp',
            'updated_at DATETIME default current_timestamp on update current_timestamp',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->createTable('email_verification_tokens');
    }

    public function down(): void
    {
        $this->forge->dropForeignKey('email_verification_tokens','user_id');
        $this->forge->dropTable('email_verification_tokens');
    }
}
