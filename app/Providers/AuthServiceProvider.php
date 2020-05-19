<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Common\Utils\TokenManager;
use App\Services\UserService;
use App\Repositories\UserRepository;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService($app[UserRepository::class]);
        });
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {

            if ($request->header('authorization')) {
                $token = $request->header('authorization');
                $token = str_replace('Bearer ', '', $token);
                $tokenData = TokenManager::decode($token);
                if ($tokenData) {
                    return $this->app[UserService::class]->detail(['id' => $tokenData['uid']], ['username', 'id', 'last_login_time', 'email']);
                }
            }
            return null;
        });
    }
}
