<?php

namespace App\Http\Middleware\Authentication;

use App\Exceptions\ApiException;
use App\Http\ErrorCodes\AuthErrorCode;
use App\Http\Responses\JsonResponse;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Klasa wywoływana przed autoryzacją
 */
class Authenticate extends Middleware
{
    /**
     * @param Request $request
     */
    protected function redirectTo($request) {

        // if (!$request->expectsJson()) {
        //     return route('login');
        // }
    }

    /**
     * @param Request $request
     * @param Closure $next
     */
    public function handle($request, Closure $next, ...$guards) {

        /** @var Request $request */

        $currentRootName = Route::currentRouteName();

        $exceptionalRouteNames = [
            'auth-login',
            'auth-register',
            'auth-forgotPassword',
            'auth-resetPassword',
            'auth-redirectToProvider',
            'auth-handleProviderCallback',
            'auth-restoreAccount'
        ];

        $independentRouteNames = [
            'defaultType-getProviderTypes',
            'brackets/admin-auth::admin',
            'admin/edit-password',
            'admin/edit-profile',
            'admin/admin-users/index',
            'admin/roles/index',
            'admin/devices/index'
        ];

        $logout = 'auth-logoutMe';

        if ($jwt = $request->cookie(env('JWT_COOKIE_NAME'))) {

            $request->headers->set('Authorization', 'Bearer ' . $jwt);
            $authenticated = true;
            $isTokenRefreshed = false;

            try {
                $this->authenticate($request, $guards);
            } catch (AuthenticationException $e) {

                JsonResponse::deleteCookie('JWT');

                /** @var PersonalAccessToken $personalAccessToken */
                $personalAccessToken = JsonResponse::isRefreshTokenValid($request);

                if ($personalAccessToken) {

                    if (in_array($currentRootName, $exceptionalRouteNames)) {
                        throw new ApiException(AuthErrorCode::REFRESH_TOKEN_IS_STILL_ACTIVE());
                    }

                    if ($currentRootName != $logout) {
                        JsonResponse::refreshToken($personalAccessToken, $request);
                        $isTokenRefreshed = true;
                    } else {
                        $authenticated = false;
                    }

                } else {

                    if ($currentRootName == $logout) {
                        throw new ApiException(AuthErrorCode::ALREADY_LOGGED_OUT());
                    }

                    if ($currentRootName &&
                        !in_array($currentRootName, $exceptionalRouteNames) &&
                        !in_array($currentRootName, $independentRouteNames))
                    {
                        throw new ApiException(AuthErrorCode::UNAUTHORIZED());
                    }

                    $authenticated = false;
                }
            }

            if ($authenticated) {

                if (in_array($currentRootName, $exceptionalRouteNames)) {
                    throw new ApiException(AuthErrorCode::ALREADY_LOGGED_IN());
                }

                /** @var User $user */
                $user = Auth::user();
                $user->checkAccess();

                if ($isTokenRefreshed) {
                    $user->checkDevice($request->device_id, 'REFRESH_TOKEN');
                }
            }

        } else {

            /** @var PersonalAccessToken $personalAccessToken */
            $personalAccessToken = JsonResponse::isRefreshTokenValid($request);

            if ($personalAccessToken) {

                if (in_array($currentRootName, $exceptionalRouteNames)) {
                    throw new ApiException(AuthErrorCode::REFRESH_TOKEN_IS_STILL_ACTIVE());
                }

                if ($currentRootName != $logout) {

                    JsonResponse::refreshToken($personalAccessToken, $request);

                    /** @var User $user */
                    $user = Auth::user();
                    $user->checkAccess();
                    $user->checkDevice($request->device_id, 'REFRESH_TOKEN');
                }

            } else {

                if ($currentRootName == $logout) {
                    throw new ApiException(AuthErrorCode::ALREADY_LOGGED_OUT());
                }

                if ($currentRootName &&
                    !in_array($currentRootName, $exceptionalRouteNames) &&
                    !in_array($currentRootName, $independentRouteNames))
                {
                    echo json_encode($currentRootName);
                    die;
                    throw new ApiException(AuthErrorCode::UNAUTHORIZED());
                }
            }
        }

        return $next($request);
    }
}
