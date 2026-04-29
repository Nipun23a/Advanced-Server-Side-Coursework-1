<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InternalServiceSecretSeeder extends Seeder
{
    public function run()
    {
        $services = [
            ['service_name' => 'notification-service', 'secret' => 'notif-secret-key-01'],
            ['service_name' => 'analytics-service',    'secret' => 'analytics-secret-key-02'],
        ];

        foreach ($services as $service) {
            $exists = $this->db->table('internal_service_secrets')
                ->where('service_name', $service['service_name'])
                ->countAllResults();

            if (! $exists) {
                $this->db->table('internal_service_secrets')->insert([
                    'service_name' => $service['service_name'],
                    'secret_hash'  => password_hash($service['secret'], PASSWORD_DEFAULT),
                    'is_active'    => 1,
                ]);
            }
        }
    }
}
