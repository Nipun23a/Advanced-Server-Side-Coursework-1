<?php

namespace App\Controllers;

use App\Libraries\InternalApiClient;

class AnalyticsController extends BaseController
{
    protected InternalApiClient $api;

    public function __construct()
    {
        $this->api = new InternalApiClient();
    }

    // ---- Overview (all 8 charts) ----------------------------------------

    public function index()
    {
        $filters        = $this->getFilters();
        $data           = $this->safeAnalyticsGet('/api/v1/analytics/all', $filters);
        $filterOptions  = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('analytics/index', [
            'title'         => 'Analytics Overview',
            'chartData'     => $data['data']          ?? [],
            'filterOptions' => $filterOptions['data'] ?? [],
            'activeFilters' => $filters,
        ]);
    }

    // ---- Individual chart pages ------------------------------------------

    public function skillsGap()
    {
        $filters        = $this->getFilters();
        $data           = $this->safeAnalyticsGet('/api/v1/analytics/skills-gap', $filters);
        $filterOptions  = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('analytics/skills_gap', [
            'title'         => 'Curriculum Skills Gap Analysis',
            'chartData'     => json_encode($data['data']          ?? []),
            'filterOptions' => $filterOptions['data']             ?? [],
            'activeFilters' => $filters,
        ]);
    }

    public function employmentSectors()
    {
        $filters        = $this->getFilters();
        $data           = $this->safeAnalyticsGet('/api/v1/analytics/employment-sectors', $filters);
        $filterOptions  = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('analytics/employment_sectors', [
            'title'         => 'Employment by Industry Sector',
            'chartData'     => json_encode($data['data']          ?? []),
            'filterOptions' => $filterOptions['data']             ?? [],
            'activeFilters' => $filters,
        ]);
    }

    public function jobTitles()
    {
        $filters        = $this->getFilters();
        $data           = $this->safeAnalyticsGet('/api/v1/analytics/job-titles', $filters);
        $filterOptions  = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('analytics/job_titles', [
            'title'         => 'Most Common Job Titles',
            'chartData'     => json_encode($data['data']          ?? []),
            'filterOptions' => $filterOptions['data']             ?? [],
            'activeFilters' => $filters,
        ]);
    }

    public function topEmployers()
    {
        $filters        = $this->getFilters();
        $data           = $this->safeAnalyticsGet('/api/v1/analytics/top-employers', $filters);
        $filterOptions  = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('analytics/top_employers', [
            'title'         => 'Top Employers',
            'chartData'     => json_encode($data['data']          ?? []),
            'filterOptions' => $filterOptions['data']             ?? [],
            'activeFilters' => $filters,
        ]);
    }

    public function certificationTrends()
    {
        $filters        = $this->getFilters();
        $data           = $this->safeAnalyticsGet('/api/v1/analytics/certification-trends', $filters);
        $filterOptions  = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('analytics/certification_trends', [
            'title'         => 'Certification Trends Over Time',
            'chartData'     => json_encode($data['data']          ?? []),
            'filterOptions' => $filterOptions['data']             ?? [],
            'activeFilters' => $filters,
        ]);
    }

    public function licenseDistribution()
    {
        $filters        = $this->getFilters();
        $data           = $this->safeAnalyticsGet('/api/v1/analytics/license-distribution', $filters);
        $filterOptions  = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('analytics/license_distribution', [
            'title'         => 'Professional License Distribution',
            'chartData'     => json_encode($data['data']          ?? []),
            'filterOptions' => $filterOptions['data']             ?? [],
            'activeFilters' => $filters,
        ]);
    }

    public function careerPathways()
    {
        $filters        = $this->getFilters();
        $data           = $this->safeAnalyticsGet('/api/v1/analytics/career-pathways', $filters);
        $filterOptions  = $this->safeAnalyticsGet('/api/v1/analytics/filters');

        return view('analytics/career_pathways', [
            'title'         => 'Career Pathways',
            'chartData'     => json_encode($data['data']          ?? []),
            'filterOptions' => $filterOptions['data']             ?? [],
            'activeFilters' => $filters,
        ]);
    }

    public function graduationOutcomes()
    {
        $data = $this->safeAnalyticsGet('/api/v1/analytics/graduation-outcomes');

        return view('analytics/graduation_outcomes', [
            'title'     => 'Graduation Outcomes Over Time',
            'chartData' => json_encode($data['data'] ?? []),
        ]);
    }

    // ---- Helpers ---------------------------------------------------------

    private function getFilters(): array
    {
        return array_filter([
            'programme'      => $this->request->getGet('programme'),
            'graduationYear' => $this->request->getGet('graduationYear'),
            'sector'         => $this->request->getGet('sector'),
            'limit'          => $this->request->getGet('limit'),
            'months'         => $this->request->getGet('months'),
        ]);
    }

    private function safeAnalyticsGet(string $endpoint, array $query = []): array
    {
        try {
            return $this->api->analyticsGet($endpoint, $query) ?? [];
        } catch (\Throwable $e) {
            log_message('error', 'AnalyticsController: ' . $endpoint . ' — ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
}
