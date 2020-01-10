<?php

namespace App\Providers;

use Laravel\Passport\Passport;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Dashboard\roles' => 'App\Policies\rolesPolicy',
        'App\Models\Dashboard\mini_dashboard' => 'App\Policies\mini_dashboardPolicy',
        'App\Models\General\category' => 'App\Policies\categoryPolicy',
        'App\Admin'=>"App\Policies\adminPolicy",
        'App\Driver'=>"App\Policies\driverPolicy"

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
       
        Passport::routes();
    }
}
