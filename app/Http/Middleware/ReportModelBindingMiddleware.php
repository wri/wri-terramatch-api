<?php

namespace App\Http\Middleware;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Closure;
use Illuminate\Http\Request;

/**
 * Implicit binding doesn't work for interfaces, so we need to figure out the concrete model class and
 * load the instance ourselves.
 */
class ReportModelBindingMiddleware
{
    private const REPORT_MODEL_CLASSES = [
        'project-reports' => ProjectReport::class,
        'site-reports' => SiteReport::class,
        'nursery-reports' => NurseryReport::class,
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!$request->route()->hasParameter('reportEntities') ||
            !$request->route()->hasParameter('report')) {
            return $next($request);
        }

        $reportEntity = $request->route()->parameter('reportEntities');
        $reportUuid = $request->route()->parameter('report');
        $modelClass = self::REPORT_MODEL_CLASSES[$reportEntity];
        abort_unless($modelClass, 404, "Report entity not found: $reportEntity");

        $report = $modelClass::isUuid($reportUuid)->first();
        abort_unless($report, 404, "Report not found [$reportEntity, $reportUuid]");

        // Because we're providing the report instance ourselves, the logic that rejiggers route context
        // parameters to the controller is short-circuited, and we therefore need to explicitly remove the
        // now superfluous reportEntities parameter. Otherwise, it will remain as the first parameters
        // passed to the controller method invocation.
        $request->route()->forgetParameter('reportEntities');
        $request->route()->setParameter('report', $report);

        return $next($request);
    }
}
