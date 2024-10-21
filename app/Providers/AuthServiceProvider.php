<?php

namespace App\Providers;

use App\Models\Experiment;
use App\Models\User;
use App\Policies\ExperimentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Experiment::class => ExperimentPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
