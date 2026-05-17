<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyEmailWithCode;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $code = rand(100000, 999999);
        $user = $request->user();
        $user->verification_code = $code;
        $user->save();

        $user->notify(new VerifyEmailWithCode($code));

        return back()->with('status', 'verification-link-sent');
    }
}
