<?php

namespace App\Http\Controllers\Traits;

use App\Models\Framework;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

trait IsAdminIndex
{
    protected function sort ($query, $sortableColumns): void
    {
        if (in_array(request()->query('sort'), $sortableColumns)) {
            $query->allowedSorts($sortableColumns);
        }
    }

    protected function isolateAuthorizedFrameworks (QueryBuilder $query, string $tableName): void
    {
        $user = Auth::user();
        $frameworks = Framework::all();

        $frameworkNamesWithPref = $frameworks->map(function ($framework) {
            return 'framework-' . $framework->slug;
        })->toArray();

        $frameworkNames = $frameworks->map(function ($framework) {
            return $framework->slug;
        })->toArray();

        if (!$user->hasAllPermissions($frameworkNamesWithPref)) {
            $query->where(function ($query) use ($tableName, $frameworkNames, $user) {
                foreach ($frameworkNames as $framework) {
                    $frameworkPermission = 'framework-' . $framework;
                    if ($user->hasPermissionTo($frameworkPermission)) {
                        $query->orWhere("$tableName.framework_key", $framework);
                    }
                }
            });
        }
    }

    protected function paginate (QueryBuilder $query): LengthAwarePaginator
    {
        $perPage = request()->query('per_page') ?? config('app.pagination_default', 15);
        return $query->paginate($perPage)->appends(request()->query());
    }
}