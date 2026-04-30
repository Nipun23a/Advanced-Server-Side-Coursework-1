<?php

namespace App\Libraries;

use App\Models\InternalServiceSecretModel;

class InternalApiClient
{
    protected $baseUrl;
    protected string $dashboardApiKey;
    protected InternalServiceSecretModel $secretModel;

    public function __construct()
    {
        $this->baseUrl         = rtrim(env('INTERNAL_API_URL') ?: 'http://localhost:3000', '/');
        $this->dashboardApiKey = env('DASHBOARD_API_KEY') ?: '';
        $this->secretModel     = new InternalServiceSecretModel();
    }

    // =========================================================================
    // INTERNAL SECRET AUTH  (X-Internal-Secret)
    // Used by all write operations and internal data reads.
    // =========================================================================

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query], 'internal');
    }

    public function post(string $endpoint, array $body = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $body], 'internal');
    }

    public function put(string $endpoint, array $body = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $body], 'internal');
    }

    public function delete(string $endpoint, array $body = []): array
    {
        $options = [];
        if (! empty($body)) {
            $options['json'] = $body;
        }
        return $this->request('DELETE', $endpoint, $options, 'internal');
    }

    // =========================================================================
    // BEARER TOKEN AUTH  (Dashboard API Key)
    // Used for analytics endpoints that require read:analytics permission.
    // The raw API key is stored in DASHBOARD_API_KEY in .env.
    // =========================================================================

    public function analyticsGet(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query], 'bearer');
    }

    // =========================================================================
    // CORE REQUEST METHOD
    // =========================================================================

    private function request(string $method, string $endpoint, array $options = [], string $authType = 'internal'): array
    {
        try {
            $headers = ['Accept' => 'application/json'];

            if ($authType === 'bearer') {
                if ($this->dashboardApiKey === '') {
                    log_message('error', 'InternalApiClient: DASHBOARD_API_KEY is not set in .env');
                    return ['success' => false, 'data' => [], 'message' => 'Analytics API key not configured.'];
                }
                $headers['Authorization'] = 'Bearer ' . $this->dashboardApiKey;
            } else {
                $headers['X-Internal-Secret'] = $this->resolveSecret();
                $headers['Content-Type']      = 'application/json';
            }

            $options['headers']     = $headers;
            $options['http_errors'] = false; // never throw on 4xx / 5xx

            $client   = \Config\Services::curlrequest();
            $response = $client->request($method, $this->baseUrl . $endpoint, $options);
            $raw      = $response->getBody();
            $body     = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', "InternalApiClient: invalid JSON from {$method} {$endpoint} — " . json_last_error_msg());
                return ['success' => false, 'data' => [], 'message' => 'Invalid response from API.'];
            }

            return $body;

        } catch (\Throwable $e) {
            log_message('error', "InternalApiClient: {$method} {$endpoint} failed — " . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'API request failed.'];
        }
    }

    private function resolveSecret(): string
    {
        $secret = $this->secretModel->getActiveSecretValue();

        if (! $secret) {
            throw new \RuntimeException('External API secret is not configured yet. Save or generate one from the Developer API Keys page.');
        }

        return $secret;
    }
}
