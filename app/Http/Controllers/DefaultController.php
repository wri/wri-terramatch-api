<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;

class DefaultController extends Controller
{
    private $jsonResponseFactory = null;

    public function __construct(JsonResponseFactory $jsonResponseFactory)
    {
        $this->jsonResponseFactory = $jsonResponseFactory;
    }

    /**
     * This method returns an empty object to keep the load balancers happy that
     * the web server is healthy.
     */
    public function indexAction(Request $request): JsonResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
