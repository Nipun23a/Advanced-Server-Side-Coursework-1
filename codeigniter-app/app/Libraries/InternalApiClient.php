<?php

namespace App\Libraries;

use App\Models\InternalServiceSecretModel;

class InternalApiClient
{
    protected $baseUrl;
    protected InternalServiceSecretModel $secretModel;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('INTERNAL_API_URL') ?: 'http://localhost:3000', '/');
        $this->secretModel = new InternalServiceSecretModel();
    }

    protected function resolveSecret(): string
    {
        $secret = $this->secretModel->getActiveSecretValue();

        if (! $secret) {
            throw new \RuntimeException('External API secret is not configured yet. Save or generate one from the Developer API Keys page.');
        }

        return $secret;
    }

    public function getSecret($method, $endpoint, $data = [] )
    {
        $client = \Config\Services::curlrequest();
        $options = [
            'headers' => [
                'X-Internal-Secret' => $this->resolveSecret(),
                'Content-Type' => 'application/json'
            ]
        ];

        if (! empty($data)) {
            if (strtoupper($method) === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }
        }

        $response = $client->request($method, $this->baseUrl . $endpoint, $options);
        return json_decode($response -> getBody(), true);
    }

    public function get($endpoint,$data = [])
    {
        return $this->getSecret('GET', $endpoint, $data);
    }

    public function post($endpoint, $data = [])
    {
        return $this->getSecret('POST', $endpoint, $data);
    }

    public function put($endpoint, $data = [])
    {
        return $this->getSecret('PUT', $endpoint, $data);
    }

    public function delete($endpoint)
    {
        return $this->getSecret('DELETE', $endpoint);
    }

}
