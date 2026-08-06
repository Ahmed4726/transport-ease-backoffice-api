<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check() && Auth::user()->role === UserRole::ADMIN && Auth::user()->status === UserStatus::APPROVED) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'Invalid credentials.'
                ])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->role !== UserRole::ADMIN) {

            Auth::logout();

            return back()->withErrors([
                'email' => 'Unauthorized access.'
            ]);
        }

        if ($user->status !== UserStatus::APPROVED) {

            Auth::logout();

            return back()
                ->withErrors([
                    'email' => 'Account is inactive.'
                ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
