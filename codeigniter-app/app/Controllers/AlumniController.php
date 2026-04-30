<?php

namespace App\Controllers;

use App\Libraries\InternalApiClient;

class AlumniController extends BaseController
{
    protected InternalApiClient $api;

    public function __construct()
    {
        $this->api = new InternalApiClient();
    }

    public function index()
    {
        $filters       = $this->getFilters();
        $data          = $this->safeAnalyticsGet('/api/v1/alumni/browse', $filters);
        $filterOptions = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('alumni/index', [
            'title'         => 'Browse Alumni',
            'alumni'        => $data['data']['alumni'] ?? [],
            'total'         => $data['data']['total']  ?? 0,
            'pagination'    => $data['data']           ?? [],
            'filterOptions' => $filterOptions['data']  ?? [],
            'activeFilters' => $filters,
        ]);
    }

    public function show(int $alumniId)
    {
        $data = $this->safeAnalyticsGet("/api/v1/alumni/{$alumniId}");

        if (empty($data['data'])) {
            return redirect()->to('/alumni')->with('error', 'Alumni profile not found.');
        }

        return view('alumni/show', [
            'title'  => 'Alumni Profile',
            'alumni' => $data['data'],
        ]);
    }

    private function getFilters(): array
    {
        return array_filter([
            'programme'      => $this->request->getGet('programme'),
            'graduationYear' => $this->request->getGet('graduationYear'),
            'sector'         => $this->request->getGet('sector'),
            'page'           => $this->request->getGet('page')  ?? 1,
            'limit'          => $this->request->getGet('limit') ?? 20,
        ]);
    }

    private function safeAnalyticsGet(string $endpoint, array $query = []): array
    {
        try {
            return $this->api->analyticsGet($endpoint, $query) ?? [];
        } catch (\Throwable $e) {
            log_message('error', 'AlumniController: ' . $endpoint . ' — ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
}
