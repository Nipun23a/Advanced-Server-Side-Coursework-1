<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSecretValueToInternalServiceSecrets extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('secret_value', 'internal_service_secrets')) {
            $this->forge->addColumn('internal_service_secrets', [
                'secret_value' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'secret_hash',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('secret_value', 'internal_service_secrets')) {
            $this->forge->dropColumn('internal_service_secrets', 'secret_value');
        }
    }
}
