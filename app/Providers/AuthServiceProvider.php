<?php

namespace App\Providers;

use App\Models\Experiment;
use App\Models\ExperimentSession;
use App\Models\User;
use App\Policies\ExperimentPolicy;
use App\Policies\ExperimentSessionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Experiment::class => ExperimentPolicy::class,
        ExperimentSession::class => ExperimentSessionPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
