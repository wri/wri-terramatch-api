<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Models\V2\AuditableModel;
use Illuminate\Http\Request;

class DeleteAuditStatusController extends Controller
{
    public function __invoke(Request $request, AuditableModel $auditable, string $uuid)
    {
        if ($this->checkAndLogRelation($auditable, 'audits', 'id', $uuid)) {
            $auditable->audits()->where('id', $uuid)->delete();
        }

        if ($this->checkAndLogRelation($auditable, 'auditStatuses', 'uuid', $uuid)) {
            $auditable->auditStatuses()->where('uuid', $uuid)->delete();
        }

        return response()->json(['message' => 'Audit log deleted successfully.'], 200);
    }

    public function checkAndLogRelation($model, $relationMethod, $column, $value)
    {
        if (method_exists($model, $relationMethod)) {
            $exists = $model->$relationMethod()->where($column, $value)->exists();

            return $exists;
        }

        return false;
    }
}
