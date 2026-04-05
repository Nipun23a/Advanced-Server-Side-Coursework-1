<?php

namespace App\Controllers;

use App\Libraries\InternalApiClient;
use App\Models\AlumniProfileModel;

class BiddingController extends BaseController
{
    protected InternalApiClient $apiClient;
    protected AlumniProfileModel $profileModel;

    public function __construct()
    {
        $this->apiClient = new InternalApiClient();
        $this->profileModel = new AlumniProfileModel();
    }

    public function index()
    {
        $userId = (int) session()->get('user_id');
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');

        return view('bidding/index', [
            'title' => 'Bidding',
            'tomorrow' => $tomorrow,
            'balance' => $this->safeApiGet("/api/v1/bids/balance?user_id={$userId}"),
            'monthlyLimit' => $this->safeApiGet("/api/v1/bids/monthly-limit?user_id={$userId}"),
            'bidStatus' => $this->safeApiGet("/api/v1/bids/status?user_id={$userId}"),
            'bidHistory' => $this->safeApiGet("/api/v1/bids/history?user_id={$userId}&page=1&limit=20"),
        ]);
    }

    public function placeBid()
    {
        $userId = (int) session()->get('user_id');
        $payload = [
            'user_id' => $userId,
            'bid_amount' => $this->request->getPost('bid_amount'),
            'bid_date' => $this->request->getPost('bid_date'),
            'sponsorship_offer_id' => $this->request->getPost('sponsorship_offer_id') ?: null,
        ];

        $response = $this->safeApiWrite('post', '/api/v1/bids', $payload);
        return redirect()->to('/bidding')->with($response['success'] ? 'success' : 'error', $response['message']);
    }

    public function updateBid(int $bidId)
    {
        $userId = (int) session()->get('user_id');
        $payload = [
            'user_id' => $userId,
            'bid_amount' => $this->request->getPost('bid_amount'),
            'sponsorship_offer_id' => $this->request->getPost('sponsorship_offer_id') ?: null,
        ];

        $response = $this->safeApiWrite('put', "/api/v1/bids/{$bidId}", $payload);
        return redirect()->to('/bidding')->with($response['success'] ? 'success' : 'error', $response['message']);
    }

    public function cancelBid(int $bidId)
    {
        $userId = (int) session()->get('user_id');
        $response = $this->safeApiWrite('delete', "/api/v1/bids/{$bidId}?user_id={$userId}");
        return redirect()->to('/bidding')->with($response['success'] ? 'success' : 'error', $response['message']);
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
                'post' => $this->apiClient->post($endpoint, $payload),
                'put' => $this->apiClient->put($endpoint, $payload),
                'delete' => $this->apiClient->delete($endpoint),
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
