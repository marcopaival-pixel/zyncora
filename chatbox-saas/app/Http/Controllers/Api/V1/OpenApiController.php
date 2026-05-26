<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class OpenApiController extends Controller
{
    public function show(): Response
    {
        $path = base_path('docs/openapi-v1.yaml');

        if (! File::exists($path)) {
            abort(404, 'OpenAPI specification not found.');
        }

        return response(File::get($path), 200, [
            'Content-Type' => 'application/yaml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
