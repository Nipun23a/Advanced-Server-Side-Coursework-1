<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCertificateUrlToCertificates extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('certificates', [
            'certificate_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 256,
                'null'       => true,
                'default'    => null,
                'after'      => 'issuer_name',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('certificates', 'certificate_url');
    }
}
