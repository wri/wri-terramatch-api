<?php

namespace App\Http\Controllers\V2\BaselineMonitoring;

use App\Helpers\V2Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\File\ImportRequest;
use App\Imports\BaselineMonitoringProjectImport;
use App\Imports\BaselineMonitoringSiteImport;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

class BaselineMonitoringImportController extends Controller
{

    public function __invoke(ImportRequest $request): JsonResponse
    {

        $validated = $request->validated();

        $model = V2Helper::getModel(
            data_get($validated, 'importable_type', ''),
            data_get($validated, 'importable_id', 0)
        );

        if(empty($model)){
            throw new ModelNotFoundException;
        }

        $this->handleImport(
            data_get($validated, 'importable_type'),
            $model,
            $request->file('upload_file')
        );

        return response()->json(['successful import'],200);
    }

    private function handleImport(string $type, EloquentModel $model, $file)
    {
        switch($type){
            case "programme":
            case "project":
            case "terrafund_programme":
                Excel::import(new BaselineMonitoringProjectImport( $model), $file);
                break;
            case "site":
            case "terrafund_site":
                Excel::import(new BaselineMonitoringSiteImport($model), $file);
        }
    }

}
