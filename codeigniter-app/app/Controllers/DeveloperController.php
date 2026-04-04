<?php

namespace App\Controllers;

class DeveloperController extends BaseController
{
    public function index()
    {
        return view('developer/api_keys');
    }

    public function apiKeys()
    {
        return view('developer/api_keys',[
            'title' => 'API Keys'
        ]);
    }

    public function apiDocs()
    {
        return view('developer/api_docs', [
            'title' => 'API Documentation'
        ]);
    }

    public function usage()
    {
        return view('developer/usage', [
            'title' => 'Usage'
        ]);
    }

}