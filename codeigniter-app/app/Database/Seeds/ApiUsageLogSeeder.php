<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ApiUsageLogSeeder extends Seeder
{
    public function run()
    {
        $logs = [
            ['email' => 'developer@example.com', 'endpoint' => '/api/v1/alumni',        'method' => 'GET',  'ip' => '192.168.1.10'],
            ['email' => 'developer@example.com', 'endpoint' => '/api/v1/alumni/1',      'method' => 'PUT',  'ip' => '192.168.1.10'],
            ['email' => 'alumni1@example.com',   'endpoint' => '/api/v1/bids',          'method' => 'POST', 'ip' => '10.0.0.5'],
            ['email' => 'alumni1@example.com',   'endpoint' => '/api/v1/alumni/2',      'method' => 'GET',  'ip' => '10.0.0.5'],
            ['email' => 'alumni2@example.com',   'endpoint' => '/api/v1/alumni/3',      'method' => 'GET',  'ip' => '172.16.0.22'],
        ];

        foreach ($logs as $log) {
            $user = $this->db->table('users')->where('email', $log['email'])->get()->getRowArray();
            if (! $user) {
                continue;
            }

            $apiKey = $this->db->table('api_keys')->where('user_id', $user['id'])->get()->getRowArray();
            if (! $apiKey) {
                continue;
            }

            $this->db->table('api_usage_logs')->insert([
                'api_key_id'  => $apiKey['id'],
                'endpoint'    => $log['endpoint'],
                'http_method' => $log['method'],
                'source_ip'   => $log['ip'],
                'access_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
