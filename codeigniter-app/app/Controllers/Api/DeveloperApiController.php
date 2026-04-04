<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\InternalApiClient;

class DeveloperApiController extends BaseController
{
    protected $apiClient;
    public function __construct()
    {
        $this -> apiClient = new InternalApiClient();
    }
    public function index()
    {
        try {
            $response = $this -> apiClient -> get('/api/v1/api-keys');
            return $this -> response -> setJSON($response);
        }catch (\Exception $e){
            return $this -> response -> setStatusCode(500) -> setJSON([
                'success' => false,
                'message' => $e -> getMessage()
            ]);
        }
    }

    public function create()
    {
        try {
            $response = $this -> apiClient -> post('/api/v1/api-keys');
            return $this -> response -> setJSON($response);
        }catch (\Exception $e){
            return $this -> response -> setStatusCode(500) -> setJSON([
                'success' => false,
                'message' => $e -> getMessage()
            ]);
        }
    }

    public function revoke($id)
    {
        try {
            $response = $this -> apiClient -> delete("/api/v1/api-keys/{$id}");
            return $this -> response -> setJSON($response);
        }catch (\Exception $e){
            return $this -> response -> setStatusCode(500) -> setJSON([
                'success' => false,
                'message' => $e -> getMessage()
            ]);
        }
    }

    public function stats($id)
    {
        try {
            $response = $this -> apiClient -> get("/api/v1/api-keys/{$id}/stats");
            return $this -> response -> setJSON($response);
        }catch (\Exception $e){
            return $this -> response -> setStatusCode(500) -> setJSON([
                'success' => false,
                'message' => $e -> getMessage()
            ]);
        }
    }


}
