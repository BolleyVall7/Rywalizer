<?php

namespace App\Http\Middleware\Authenticate;

use App\Http\Libraries\Encrypter\Encrypter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Symfony\Component\HttpFoundation\Response;

class BeforeAuthenticate
{
    public function handle(Request $request, Closure $next) {

        if ($request->cookie('JWT')) {
            return response([
                'code' => 'A8',
                'message' => 'You are already logged in!'
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        $request->validate([
            'email' => 'required|string|email|max:254'
        ]);

        $forgotPasswordURL = env('APP_URL') . '/api/forgot-password';
        $resetPasswordURL = env('APP_URL') . '/api/reset-password';

        if ($request->url() != $forgotPasswordURL) {

            $request->validate([
                'password' => 'required|string|between:8,20'
            ]);

            if ($request->url() == $resetPasswordURL) {
                $request->validate([
                    'password' => ['confirmed', RulesPassword::defaults()],
                    'token' => 'required|string|alpha_num|size:64'
                ]);
            }
        }

        $encrypter = new Encrypter;

        $request->merge(['email' => $encrypter->encrypt($request->input('email'), 254)]);

        return $next($request);
    }
}
