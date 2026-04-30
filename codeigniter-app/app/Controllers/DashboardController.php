<?php

namespace App\Controllers;

use App\Libraries\InternalApiClient;
use App\Models\AlumniProfileModel;
use App\Models\CertificateModel;
use App\Models\DegreeModel;
use App\Models\EmploymentHistoryModel;
use App\Models\LicenseModel;

class DashboardController extends BaseController
{
    protected InternalApiClient $api;

    public function __construct()
    {
        $this->api = new InternalApiClient();
    }

    public function index()
    {
        $role = session()->get('user_role');

        if ($role === 'alumni') {
            return $this->alumniDashboard();
        }

        if ($role === 'developer') {
            return redirect()->to('/developer/api-keys');
        }

        // admin — analytics overview dashboard
        $summary  = $this->safeAnalyticsGet('/api/v1/analytics/summary');
        $featured = $this->safeAnalyticsGet('/api/v1/public/featured-alumni/today');

        return view('dashboard/index', [
            'title'    => 'Dashboard',
            'summary'  => $summary['data']  ?? [],
            'featured' => $featured['data'] ?? null,
        ]);
    }

    // ---- Alumni personal dashboard ----------------------------------------

    private function alumniDashboard()
    {
        $userId    = (int) session()->get('user_id');
        $profile   = (new AlumniProfileModel())->findByUserId($userId);
        $profileId = $profile['id'] ?? null;

        $degrees     = [];
        $certificates = [];
        $licenses    = [];
        $latestJob   = null;

        if ($profileId) {
            $degrees      = (new DegreeModel())->where('profile_id', $profileId)->orderBy('completion_date', 'DESC')->findAll();
            $certificates = (new CertificateModel())->where('profile_id', $profileId)->orderBy('completion_date', 'DESC')->findAll();
            $licenses     = (new LicenseModel())->where('profile_id', $profileId)->orderBy('completion_date', 'DESC')->findAll();
            $employment   = (new EmploymentHistoryModel())->where('profile_id', $profileId)->orderBy('start_date', 'DESC')->findAll();
            $latestJob    = $employment[0] ?? null;
        }

        $balance    = $this->safeGet("/api/v1/bids/balance?user_id={$userId}");
        $bidStatus  = $this->safeGet("/api/v1/bids/status?user_id={$userId}");
        $recentBids = $this->safeGet("/api/v1/bids/history?user_id={$userId}&page=1&limit=5");

        $sponsorBalance = [];
        $pendingOffers  = [];
        if ($profileId) {
            $sponsorBalance = $this->safeGet("/api/v1/sponsorships/balance?alumni_id={$profileId}");
            $pendingOffers  = $this->safeGet("/api/v1/sponsorships/offers?alumni_id={$profileId}&status=pending");
        }

        return view('dashboard/alumni', [
            'title'         => 'My Dashboard',
            'profile'       => $profile,
            'degrees'       => $degrees,
            'certificates'  => $certificates,
            'licenses'      => $licenses,
            'latestJob'     => $latestJob,
            'balance'       => $balance,
            'bidStatus'     => $bidStatus,
            'recentBids'    => $recentBids,
            'sponsorBalance'=> $sponsorBalance,
            'pendingOffers' => $pendingOffers,
        ]);
    }

    // ---- Helpers -----------------------------------------------------------

    private function safeAnalyticsGet(string $endpoint): array
    {
        try {
            return $this->api->analyticsGet($endpoint) ?? [];
        } catch (\Throwable $e) {
            log_message('error', 'DashboardController: ' . $endpoint . ' — ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    private function safeGet(string $endpoint): array
    {
        try {
            return $this->api->get($endpoint) ?? [];
        } catch (\Throwable $e) {
            log_message('error', 'DashboardController: ' . $endpoint . ' — ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }
}
