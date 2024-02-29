<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardDocumentationController extends Controller
{
    public function readAsYamlAction(Request $request): Response
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $url = config('app.url');
        $host = str_replace(['http://', 'https://'], '', $url);
        $scheme = substr($url, 0, 5) == 'https' ? 'https' : 'http';
        $file = __DIR__ . '/../../../resources/docs/swagger_description.md';
        $description = str_replace(["\n", '"'], ['\\n', '\\'], file_get_contents($file));
        $replacements = [
            '{{ HOST }}' => $host,
            '{{ SCHEME }}' => $scheme,
            '{{ DESCRIPTION }}' => $description,
        ];
        $yaml = str_replace(
            array_keys($replacements),
            array_values($replacements),
            file_get_contents(__DIR__ . '/../../../resources/docs/dashboard.yml')
        );

        return new Response($yaml, 200, ['Content-Type' => 'text/plain']);
    }
}
