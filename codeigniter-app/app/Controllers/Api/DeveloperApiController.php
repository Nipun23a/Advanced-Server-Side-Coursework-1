<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\InternalApiClient;
use App\Models\InternalServiceSecretModel;

class DeveloperApiController extends BaseController
{
    protected $apiClient;
    protected InternalServiceSecretModel $secretModel;

    public function __construct()
    {
        $this->apiClient = new InternalApiClient();
        $this->secretModel = new InternalServiceSecretModel();
    }
    private function getUserId()
    {
        $userId = session()->get('user_id');

        if (!$userId) {
            throw new \Exception('User not authenticated (session missing user_id)');
        }

        return $userId;
    }

    public function index()
    {
        try {
            $response = $this->apiClient->get('/api/v1/api-keys', [
                'user_id' => $this->getUserId()
            ]);

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function create()
    {
        try {
            $json = $this->request->getJSON(true) ?? [];
            $clientType = $json['client_type'] ?? $this->request->getPost('client_type');
            $clientType = is_string($clientType) ? trim($clientType) : '';

            $allowedClientTypes = ['analytics_dashboard', 'ar_app', 'third_party'];

            if ($clientType === '' || !in_array($clientType, $allowedClientTypes, true)) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'client_type must be one of: analytics_dashboard, ar_app, third_party.',
                ]);
            }

            $response = $this->apiClient->post('/api/v1/api-keys', [
                'user_id' => $this->getUserId(),
                'client_type' => $clientType,
            ]);

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function revoke($id)
    {
        try {
            $response = $this->apiClient->delete(
                "/api/v1/api-keys/{$id}?user_id=" . $this->getUserId()
            );

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function stats($id)
    {
        try {
            $response = $this->apiClient->get("/api/v1/api-keys/{$id}/stats", [
                'user_id' => $this->getUserId()
            ]);

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function secretStatus()
    {
        try {
            $secret = $this->secretModel->getActiveSecret();

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'configured' => $secret !== null,
                    'service_name' => InternalServiceSecretModel::EXPRESS_API_SERVICE,
                    'updated_at' => $secret['updated_at'] ?? null,
                    'created_at' => $secret['created_at'] ?? null,
                ],
                'message' => $secret
                    ? 'External API secret is configured.'
                    : 'External API secret is not configured.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function saveSecret()
    {
        try {
            $json = $this->request->getJSON(true);
            $secret = trim((string) (($json['secret'] ?? null) ?? $this->request->getPost('secret') ?? ''));

            if ($secret === '') {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Secret is required.',
                ]);
            }

            $saved = $this->secretModel->replaceActiveSecret($secret);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'configured' => true,
                    'service_name' => $saved['service_name'],
                    'updated_at' => $saved['updated_at'],
                ],
                'message' => 'External API secret saved successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function generateSecret()
    {
        try {
            $secret = bin2hex(random_bytes(64));
            $saved = $this->secretModel->replaceActiveSecret($secret);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'secret' => $secret,
                    'configured' => true,
                    'service_name' => $saved['service_name'],
                    'updated_at' => $saved['updated_at'],
                    'warning' => 'Save this external API secret now. It will not be shown again.',
                ],
                'message' => 'External API secret generated successfully.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
