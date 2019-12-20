<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\View;
use Illuminate\Config\Repository as Config;

class DocumentationController extends Controller
{
    private $viewFactory = null;
    private $config = null;

    public function __construct(ViewFactory $viewFactory, Config $config)
    {
        $this->viewFactory = $viewFactory;
        $this->config = $config;
    }

    public function readAsHtmlAction(Request $request): View
    {
        $this->authorize("yes", "App\\Models\\Default");
        return $this->viewFactory->make("Documentation/readAsHtml");
    }

    /**
     * This method serves as an API for our Swagger file. It's got a wrapper to
     * allow us to apply some crude templating to the Yaml before the response is
     * sent. By doing this we can populate the domain and protocols dynamically
     * rather than using example domains.
     */
    public function readAsYamlAction(Request $request): Response
    {
        $this->authorize("yes", "App\\Models\\Default");
        $yaml = file_get_contents(__DIR__ . "/../../../swagger.yml");
        $url = $this->config->get("app.url");
        $description = file_get_contents(__DIR__ . "/../../../swagger_description.md");
        $replacements = [
            "{{ HOST }}" => str_replace(["http://", "https://"], "", $url),
            "{{ SCHEME }}" => substr($url, 0, 5) == "https" ? "https" : "http",
            "{{ DESCRIPTION }}" => str_replace(["\n", "\""], ["\\n", "\\"], $description)
        ];
        $yaml = str_replace(array_keys($replacements), array_values($replacements), $yaml);
        return new Response($yaml, 200, ["Content-Type" => "text/plain"]);
    }
}
