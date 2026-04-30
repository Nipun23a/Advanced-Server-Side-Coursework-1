<?php

namespace App\Controllers;

use App\Libraries\InternalApiClient;

class DashboardController extends BaseController
{
    protected InternalApiClient $api;

    public function __construct()
    {
        $this->api = new InternalApiClient();
    }

    public function index()
    {
        $summary  = $this->safeAnalyticsGet('/api/v1/analytics/summary');
        $filters  = $this->safeAnalyticsGet('/api/v1/analytics/filters');
        $featured = $this->safeAnalyticsGet('/api/v1/public/featured-alumni/today');

        return view('dashboard/index', [
            'title'    => 'Dashboard',
            'summary'  => $summary['data']  ?? [],
            'filters'  => $filters['data']  ?? [],
            'featured' => $featured['data'] ?? null,
        ]);
    }

    private function safeAnalyticsGet(string $endpoint): array
    {
        try {
            return $this->api->analyticsGet($endpoint) ?? [];
        } catch (\Throwable $e) {
            log_message('error', 'DashboardController: ' . $endpoint . ' — ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
}
