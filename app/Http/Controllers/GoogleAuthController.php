<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(\Illuminate\Http\Request $request)
    {
        Log::info('Google OAuth callback started', [
            'code' => $request->has('code'),
            'state' => $request->has('state'),
        ]);

        try {
            $googleUser = Socialite::driver('google')->user();

            Log::info('Google user retrieved', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
            ]);

            $user = User::updateOrCreate(
                ['google_id' => $googleUser->getId()],
                [
                    'email' => $googleUser->getEmail(),
                    'firstname' => $googleUser->getRaw()['given_name'] ?? null,
                    'lastname' => $googleUser->getRaw()['family_name'] ?? null,
                    'avatar' => $googleUser->getAvatar(),
                ]
            );

            Log::info('User found/created', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            Auth::login($user, true);
            session()->regenerate();

            Log::info('Login completed', [
                'auth_check' => Auth::check(),
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
            ]);

            return redirect('/admin');
        } catch (\Exception $e) {
            Log::error('Google OAuth failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/admin/login')->withErrors(['google' => 'Failed to authenticate with Google.']);
        }
    }
}
