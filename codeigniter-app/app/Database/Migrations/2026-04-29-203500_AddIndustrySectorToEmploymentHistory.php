<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndustrySectorToEmploymentHistory extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('employment_history') && ! $this->db->fieldExists('industry_sector', 'employment_history')) {
            $this->forge->addColumn('employment_history', [
                'industry_sector' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'company_name',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('employment_history') && $this->db->fieldExists('industry_sector', 'employment_history')) {
            $this->forge->dropColumn('employment_history', 'industry_sector');
        }
    }
}
