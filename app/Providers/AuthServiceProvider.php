<?php

namespace App\Providers;

use Exception;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::guessPolicyNamesUsing(function (string $model): string {
            $parts = explode('\\', $model);
            if (count($parts) === 4) {
                $namespace = $parts[2];
                $name = $parts[3];

                return 'App\\Policies\\' . $namespace . '\\' . $name . 'Policy';
            } elseif (count($parts) === 5) {
                $namespace1 = $parts[2];
                $namespace2 = $parts[3];
                $name = $parts[4];

                return 'App\\Policies\\' . $namespace1 . '\\' . $namespace2 . '\\' . $name . 'Policy';
            } else {
                if (count($parts) != 3) {
                    throw new Exception();
                }
                $name = $parts[2];

                return 'App\\Policies\\' . $name . 'Policy';
            }
        });
    }
}
