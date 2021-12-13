<?php

namespace App\Http\Middleware\Authenticate;

use App\Http\Libraries\Encrypter\Encrypter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password as RulesPassword;

/**
 * Klasa wywoływana przed uwierzytelnieniem
 */
class BeforeAuthenticate
{
    /**
     * @param Illuminate\Http\Request $request
     * @param Closure $next
     */
    public function handle(Request $request, Closure $next) {

        $routeName = Route::currentRouteName();

        $login = 'auth-login';
        $register = 'auth-register';
        $forgotPassword = 'auth-forgotPassword';
        $resetPassword = 'auth-resetPassword';
        $verifyEmail = 'auth-verifyEmail';
        $updateUser = 'auth-updateUser';
        $uploadAvatar = 'auth-uploadAvatar';

        $encrypter = new Encrypter;

        if ($routeName == $login ||
            $routeName == $register ||
            $routeName == $forgotPassword ||
            $routeName == $updateUser)
        {
            $request->validate([
                'email' => 'required|string|email|max:254'
            ]);

            if ($request->email) {
                $encryptedEmail = $encrypter->encrypt($request->email, 254);
                $request->merge(['email' => $encryptedEmail]);
            }

            if ($routeName == $forgotPassword) {
                $request->validate([
                    'email' => 'exists:users'
                ]);
            }
        }

        if ($routeName == $login ||
            $routeName == $register ||
            $routeName == $resetPassword ||
            $routeName == $updateUser)
        {
            $request->validate([
                'password' => 'nullable|string|between:8,20'
            ]);

            if ($routeName != $updateUser) {
                $request->validate([
                    'password' => 'required'
                ]);
            }

            if ($routeName != $login) {
                
                $request->validate([
                    'password' => ['confirmed', RulesPassword::defaults()]
                ]);

                if ($request->password) {
                    $encryptedPassword = $encrypter->hash($request->password);
                    $request->merge(['password' => $encryptedPassword]);
                }
            }
        }
        
        if ($routeName == $resetPassword ||
            $routeName == $verifyEmail)
        {
            $request->validate([
                'token' => 'required|string|alpha_num|size:48'
            ]);

            if ($request->token) {
                $encryptedToken = $encrypter->encrypt($request->token);
                $request->merge(['token' => $encryptedToken]);
            }

            if ($routeName == $resetPassword) {
                $request->validate([
                    'token' => 'exists:password_resets',
                    'do_not_logout' => 'nullable|boolean'
                ]);
            }
        }

        if ($routeName == $updateUser) {

            $request->validate([
                'telephone' => 'nullable|string|max:24',
                'facebook_profile' => 'nullable|string|url|max:255',
                'instagram_profile' => 'nullable|string|url|max:255'
            ]);

            if ($request->telephone) {
                $encryptedTelephone = $encrypter->encrypt($request->telephone, 24);
                $request->merge(['telephone' => $encryptedTelephone]);
            }

            if ($request->facebook_profile) {
                $encryptedFacebookProfile = $encrypter->encrypt($request->facebook_profile, 255);
                $request->merge(['facebook_profile' => $encryptedFacebookProfile]);
            }

            if ($request->instagram_profile) {
                $encryptedInstagramProfile = $encrypter->encrypt($request->instagram_profile, 255);
                $request->merge(['instagram_profile' => $encryptedInstagramProfile]);
            }
        }

        if ($routeName == $uploadAvatar) {
            $request->validate([
                'avatar' => 'nullable|image|max:2048',
            ]);
        }

        return $next($request);
    }
}
