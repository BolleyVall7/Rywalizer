<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::none();
        });

        RateLimiter::for('defaultAuthLimit', function (Request $request) {
            return Limit::perMinute(env('DEFAULT_AUTH_RATE_LIMITER_PER_MINUTE'))->by($request->user());
        });

        RateLimiter::for('loginLimit', function (Request $request) {
            return Limit::perDay(env('DEFAULT_AUTH_RATE_LIMITER_PER_DAY'))->by($request->ip());
        });

        RateLimiter::for('registerLimit', function (Request $request) {
            return Limit::perDay(env('DEFAULT_AUTH_RATE_LIMITER_PER_DAY'))->by($request->ip());
        });

        RateLimiter::for('forgotPasswordLimit', function (Request $request) {
            return Limit::perDay(env('DEFAULT_AUTH_RATE_LIMITER_PER_DAY'))->by($request->ip());
        });

        RateLimiter::for('resetPasswordLimit', function (Request $request) {
            return Limit::perDay(env('DEFAULT_AUTH_RATE_LIMITER_PER_DAY'))->by($request->ip());
        });

        RateLimiter::for('refreshTokenLimit', function (Request $request) {
            return Limit::perDay(env('DEFAULT_AUTH_RATE_LIMITER_PER_DAY'))->by($request->ip());
        });

        RateLimiter::for('providerRedirectLimit', function (Request $request) {
            return Limit::perDay(env('DEFAULT_AUTH_RATE_LIMITER_PER_DAY'))->by($request->ip());
        });

        RateLimiter::for('logoutOtherDevicesLimit', function (Request $request) {
            return Limit::perDay(5)->by($request->user()->id);
        });
    }
}
