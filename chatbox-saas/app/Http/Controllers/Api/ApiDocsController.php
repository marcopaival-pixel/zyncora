<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ApiDocsController extends Controller
{
    public function index(): View
    {
        return view('api.docs', [
            'openApiUrl' => url('/api/v1/openapi.yaml'),
        ]);
    }
}
