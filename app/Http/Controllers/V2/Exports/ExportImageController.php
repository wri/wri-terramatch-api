<?php

namespace App\Http\Controllers\V2\Exports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExportImageController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'imageUrl' => 'required|url',
        ]);

        $imageUrl = $request->input('imageUrl');

        $imageContent = Http::get($imageUrl)->body();

        $filename = basename($imageUrl);

        return response($imageContent)
            ->header('Content-Type', 'image/jpeg')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
