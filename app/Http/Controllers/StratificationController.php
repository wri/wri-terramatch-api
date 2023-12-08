<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StratificationController extends Controller
{
    public function downloadTemplateAction(Request $request): BinaryFileResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        $filename = 'stratification_example.png';
        $path = base_path('resources/templates/stratification_example.png');
        $headers = [
            'Content-Type' => 'image/png',
        ];

        return response()->download($path, $filename, $headers);
    }
}
