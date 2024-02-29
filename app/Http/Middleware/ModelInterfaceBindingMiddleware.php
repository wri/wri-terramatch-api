<?php

namespace App\Http\Middleware;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Closure;
use Illuminate\Http\Request;

/**
 * Implicit binding doesn't work for interfaces, so we need to figure out the concrete model class and
 * load the instance ourselves.
 */
class ModelInterfaceBindingMiddleware
{
    private const CONCRETE_MODELS = [
        // EntityModel concrete classes
        'projects' => Project::class,
        'project' => Project::class,
        'sites' => Site::class,
        'site' => Site::class,
        'nurseries' => Nursery::class,
        'nursery' => Nursery::class,

        // ReportModel (which extends EntityModel) concrete classes
        'project-reports' => ProjectReport::class,
        'project-report' => ProjectReport::class,
        'site-reports' => SiteReport::class,
        'site-report' => SiteReport::class,
        'nursery-reports' => NurseryReport::class,
        'nursery-report' => NurseryReport::class,
    ];

    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $parameterKeys = array_keys($route->parameters);
        $modelSlugIndex = array_search('modelSlug' , array_keys($route->parameters));
        if ($modelSlugIndex < 0 || count($parameterKeys) <= $modelSlugIndex) {
            return $next($request);
        }

        $modelSlug = $route->parameter('modelSlug');
        $concreteClass = self::CONCRETE_MODELS[$modelSlug];
        abort_unless($concreteClass, 404, "Concrete class not found for model interface $modelSlug");

        // assume the model key (e.g. "report") is the next param down the list from the interface name.
        $modelParameter = $parameterKeys[$modelSlugIndex + 1];
        $modelId = $route->parameter($modelParameter);
        abort_unless($modelId, 404, "Model ID not found for $concreteClass");

        $instance = app()->make($concreteClass);
        $model = $instance->resolveRouteBinding($modelId, $route->bindingFieldFor($modelParameter));
        abort_unless($model, 404, "Model not found [$concreteClass, $modelId]");

        // Because we're providing the report instance ourselves, the logic that rejiggers route context
        // parameters to the controller is short-circuited, and we therefore need to explicitly remove the
        // now superfluous modelSlug parameter. Otherwise, it will remain as the first parameter
        // passed to the controller method invocation.
        $request->route()->forgetParameter('modelSlug');
        $request->route()->setParameter($modelParameter, $model);

        return $next($request);
    }
}
