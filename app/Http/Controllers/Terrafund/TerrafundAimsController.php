<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Terrafund\TerrafundProgramme;
use App\Resources\Terrafund\TerrafundAimResource;
use Illuminate\Http\Request;

class TerrafundAimsController extends Controller
{
    public function readAction(Request $request, TerrafundProgramme $terrafundProgramme)
    {
        $this->authorize('read', $terrafundProgramme);

        return JsonResponseHelper::success(new TerrafundAimResource($terrafundProgramme), 200);
    }
}
