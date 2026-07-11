<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $guard = Auth::guard();
        $guardName = $guard->getName();

        return response()->json([
            'auth_check' => Auth::check(),
            'guard' => $guardName,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->role,
            'session_id' => session()->getId(),
            'session_keys' => array_keys(session()->all()),
            'session_user_key' => session()->get($guardName),
        ]);
    }
}
