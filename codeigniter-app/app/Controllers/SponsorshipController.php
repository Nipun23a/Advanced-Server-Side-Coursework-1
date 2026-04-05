<?php

namespace App\Controllers;

use App\Libraries\InternalApiClient;
use App\Models\AlumniProfileModel;

class SponsorshipController extends BaseController
{
    protected InternalApiClient $apiClient;
    protected AlumniProfileModel $profileModel;

    public function __construct()
    {
        $this->apiClient = new InternalApiClient();
        $this->profileModel = new AlumniProfileModel();
    }

    public function offers()
    {
        $userId = (int) session()->get('user_id');
        $profile = $this->profileModel->findByUserId($userId);

        if (! $profile) {
            return redirect()->to('/profile')->with('error', 'Create your profile first.');
        }

        $alumniId = (int) $profile['id'];

        return view('sponsorship/offers', [
            'title' => 'Sponsorships',
            'offers' => $this->safeApiGet("/api/v1/sponsorships/offers?alumni_id={$alumniId}"),
            'balance' => $this->safeApiGet("/api/v1/sponsorships/balance?alumni_id={$alumniId}"),
            'summary' => $this->safeApiGet("/api/v1/sponsorships/summary?alumni_id={$alumniId}"),
        ]);
    }

    public function respond(int $offerId)
    {
        $userId = (int) session()->get('user_id');
        $profile = $this->profileModel->findByUserId($userId);

        if (! $profile) {
            return redirect()->to('/profile')->with('error', 'Create your profile first.');
        }

        $payload = [
            'alumni_id' => (int) $profile['id'],
            'action' => $this->request->getPost('action'),
        ];

        $response = $this->safeApiWrite('put', "/api/v1/sponsorships/offers/{$offerId}", $payload);
        return redirect()->to('/sponsorship/offers')->with($response['success'] ? 'success' : 'error', $response['message']);
    }

    protected function safeApiGet(string $endpoint): array
    {
        try {
            return $this->apiClient->get($endpoint) ?? [];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function safeApiWrite(string $method, string $endpoint, array $payload = []): array
    {
        try {
            $result = match ($method) {
                'put' => $this->apiClient->put($endpoint, $payload),
                'post' => $this->apiClient->post($endpoint, $payload),
                default => ['success' => false, 'message' => 'Unsupported method.'],
            };

            return [
                'success' => (bool) ($result['success'] ?? false),
                'message' => $result['message'] ?? (($result['error']['message'] ?? 'Request failed.')),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
