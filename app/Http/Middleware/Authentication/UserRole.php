<?php

namespace App\Http\Middleware\Authentication;

use App\Exceptions\ApiException;
use App\Http\ErrorCodes\BaseErrorCode;
use App\Http\Libraries\FieldConversion\FieldConversion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Permissions\RolePermission;
use Illuminate\Support\Facades\Route;

/**
 * Klasa wywoływana w celu sprawdzenia roli użytkownika i przyznania dostępu
 */
class UserRole
{
    /**
     * @param Illuminate\Http\Request $request
     * @param Closure $next
     */
    public function handle(Request $request, Closure $next) {

        /** @var User $user */
        $user = Auth::user();

        $roleType = json_decode($user->roleType);

        $routeName = Route::currentRouteName();

        if (!$this->checkUserPermissions($routeName, $roleType->access_level)) {
            throw new ApiException(BaseErrorCode::PERMISSION_DENIED());
        }

        return $next($request);
    }

    /**
     * Metoda sprawdza czy użytkownik ma dostęp do wybranego endpointu
     * 
     * @param string $routeName nazwa endpointu
     * @param string $detailedUserAccessLevel dla specyficznych przypadków [cyfra + litera]
     * 
     * @return bool
     */
    private function checkUserPermissions(string $routeName, string $detailedUserAccessLevel): bool {

        $userAccessLevel = 0;
        $detailedUserAccessLevelLength = strlen($detailedUserAccessLevel);

        for ($i=0; $i<$detailedUserAccessLevelLength; $i++) {

            if (!is_numeric($detailedUserAccessLevel[$i])) {
                break;
            }

            $userAccessLevel *= 10;
            $userAccessLevel += (int) $detailedUserAccessLevel[$i];
        }

        $rolePermissions = RolePermission::getMinimumAccessLevel();
        $access = false;

        for ($i=$userAccessLevel; $i>0; $i--) {

            if (isset($rolePermissions[$i])) {

                foreach ($rolePermissions[$i] as $rP) {
                    if ($rP == $routeName) {
                        $access = true;
                        break;
                    }
                }

                if ($access) {

                    if (isset($rolePermissions['exceptions'][$detailedUserAccessLevel])) {
                        foreach ($rolePermissions['exceptions'][$detailedUserAccessLevel] as $rP) {
                            if ($rP == $routeName) {
                                $access = false;
                                break;
                            }
                        }
                    }
    
                    break;
                }
            }
        }

        return $access;
    }
}
