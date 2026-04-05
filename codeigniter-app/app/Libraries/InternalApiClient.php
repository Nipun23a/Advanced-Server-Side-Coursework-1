<?php

namespace App\Libraries;

class InternalApiClient
{
    protected $baseUrl;
    protected $secret;

    public function __construct()
    {
        $this->baseUrl = env('INTERNAL_API_URL');
        $this->secret = env('INTERNAL_API_SECRET');
    }

    public function getSecret($method, $endpoint, $data = [] )
    {
        $client = \Config\Services::curlrequest();
        $options = [
            'headers' => [
                'X-Internal-Secret' => $this->secret,
                'Content-Type' => 'application/json'
            ]
        ];
        if (!empty($data)){
            $options['json'] = $data;
        }

        $response = $client -> request($method, $this->baseUrl . $endpoint, $options);
        return json_decode($response -> getBody(), true);
    }

    public function get($endpoint,$data = [])
    {
        return $this->getSecret('GET', $endpoint, $data);
    }

    public function post($endpoint, $data = [])
    {
        return $this->getSecret('POST', $endpoint, $data);
    }

    public function put($endpoint, $data = [])
    {
        return $this->getSecret('PUT', $endpoint, $data);
    }

    public function delete($endpoint)
    {
        return $this->getSecret('DELETE', $endpoint);
    }

}