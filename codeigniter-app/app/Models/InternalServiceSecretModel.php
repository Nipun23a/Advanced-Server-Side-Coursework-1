<?php

namespace App\Models;

use CodeIgniter\Model;

class InternalServiceSecretModel extends Model
{
    public const EXPRESS_API_SERVICE = 'express_api';

    protected $table = 'internal_service_secrets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = [
        'service_name',
        'secret_hash',
        'secret_value',
        'is_active',
    ];

    public function getActiveSecret(string $serviceName = self::EXPRESS_API_SERVICE): ?array
    {
        return $this->where('service_name', $serviceName)
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function getActiveSecretValue(string $serviceName = self::EXPRESS_API_SERVICE): ?string
    {
        $secret = $this->getActiveSecret($serviceName);

        if (! $secret) {
            return null;
        }

        return $secret['secret_value'] ?? null;
    }

    public function replaceActiveSecret(string $rawSecret, string $serviceName = self::EXPRESS_API_SERVICE): array
    {
        $rawSecret = trim($rawSecret);

        if ($rawSecret === '') {
            throw new \InvalidArgumentException('Secret is required.');
        }

        $this->db->transStart();

        $this->where('service_name', $serviceName)
            ->where('is_active', 1)
            ->set([
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();

        $payload = [
            'service_name' => $serviceName,
            'secret_hash' => hash('sha256', $rawSecret),
            'secret_value' => $rawSecret,
            'is_active' => 1,
        ];

        $this->insert($payload);
        $secretId = (int) $this->getInsertID();

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Failed to save internal service secret.');
        }

        $saved = $this->find($secretId);

        if (! $saved) {
            throw new \RuntimeException('Secret was saved but could not be reloaded.');
        }

        return $saved;
    }
}
