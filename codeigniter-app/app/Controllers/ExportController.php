<?php

namespace App\Controllers;

use App\Libraries\InternalApiClient;

class ExportController extends BaseController
{
    protected InternalApiClient $api;

    public function __construct()
    {
        $this->api = new InternalApiClient();
    }

    public function index()
    {
        $filterOptions = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('export/index', [
            'title'         => 'Export Data',
            'filterOptions' => $filterOptions['data'] ?? [],
        ]);
    }

    public function exportCsv()
    {
        $filters = array_filter([
            'programme'      => $this->request->getGet('programme'),
            'graduationYear' => $this->request->getGet('graduationYear'),
            'sector'         => $this->request->getGet('sector'),
            'limit'          => 1000,
        ]);

        $data   = $this->safeAnalyticsGet('/api/v1/alumni/browse', $filters);
        $alumni = $data['data']['alumni'] ?? [];

        $filename = 'alumni_export_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Email',
            'Programme',
            'Graduation Year',
            'Current Employer',
            'Current Job Title',
            'LinkedIn URL',
        ]);

        foreach ($alumni as $person) {
            fputcsv($output, [
                $person['email']            ?? '',
                $person['programme']        ?? '',
                $person['graduation_year']  ?? '',
                $person['current_employer'] ?? '',
                $person['current_role']     ?? '',
                $person['linkedin_url']     ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportPdf()
    {
        $filters = array_filter([
            'programme'      => $this->request->getGet('programme'),
            'graduationYear' => $this->request->getGet('graduationYear'),
        ]);

        $chartData     = $this->safeAnalyticsGet('/api/v1/analytics/all', $filters);
        $filterOptions = $this->safeAnalyticsGet('/api/v1/analytics/filters');
        $summary       = $this->safeAnalyticsGet('/api/v1/analytics/summary');

        return view('export/pdf', [
            'title'         => 'Generate PDF Report',
            'chartData'     => json_encode($chartData['data'] ?? []),
            'summary'       => $summary['data']               ?? [],
            'filterOptions' => $filterOptions['data']         ?? [],
            'activeFilters' => $filters,
        ]);
    }

    private function safeAnalyticsGet(string $endpoint, array $query = []): array
    {
        try {
            return $this->api->analyticsGet($endpoint, $query) ?? [];
        } catch (\Throwable $e) {
            log_message('error', 'ExportController: ' . $endpoint . ' — ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
}
