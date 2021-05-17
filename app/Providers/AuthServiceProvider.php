<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Exception;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * This property is empty because we have configured auto policy discovery
     * using the callback in the boot method. Providing the policies are named
     * correctly and in the correct directory everything should fall into place.
     */
    protected $policies = [];

    public function boot()
    {
        $this->registerPolicies();
        Gate::guessPolicyNamesUsing(function(string $model): string {
            $parts = explode("\\", $model);
            if (count($parts) != 3) {
                throw new Exception();
            }
            $name = $parts[2];
            return "App\\Policies\\" . $name . "Policy";
        });
    }
}
