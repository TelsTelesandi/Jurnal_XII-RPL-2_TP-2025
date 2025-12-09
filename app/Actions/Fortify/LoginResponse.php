<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->has('redirect') && $request->redirect === 'forum') {
            return redirect()->route('forum.widget');
        }
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required|captcha',
        ]);


        return redirect()->intended(config('fortify.home'));
    }

}
