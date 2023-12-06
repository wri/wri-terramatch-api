<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function readAsHtmlAction(Request $request, $ui = 'redoc'): View
    {
        $this->authorize('yes', 'App\\Models\\Default');

        if (strtolower($ui) === 'swagger') {
            return ViewFactory::make('Documentation/readAsHtmlSwagger');
        }

        return ViewFactory::make('Documentation/readAsHtmlRedoc');
    }

    /**
     * This method serves as an API for our Swagger file. It's got a wrapper to
     * allow us to apply some crude templating to the Yaml before the response is
     * sent. By doing this we can populate the domain and protocols dynamically
     * rather than using example domains.
     */
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
            file_get_contents(__DIR__ . '/../../../resources/docs/swagger.yml')
        );

        return new Response($yaml, 200, ['Content-Type' => 'text/plain']);
    }
}
