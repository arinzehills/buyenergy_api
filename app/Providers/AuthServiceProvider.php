<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        
        ResetPassword::createUrlUsing(function ($user, string $token) {
            //   $url='http://localhost:3000/resetPassword?token='.$token;
              $url='https://buyenergy.netlify.app/resetPassword?token='.$token;

            // return 'https://example.com/reset-password?token='.$token;
            return $url;
        });
    }
}
