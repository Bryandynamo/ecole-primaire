<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\UserLoggedIn;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('registre.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Send login notification
            try {
                $user = Auth::user();
                $user?->notify(new UserLoggedIn($request));
            } catch (\Throwable $e) {
                Log::warning('Login notification failed: '.$e->getMessage());
            }

            // Set current academic session to the enseignant's session if available
            try {
                $user = Auth::user();
                if ($user) {
                    $enseignant = \App\Models\Enseignant::where('user_id', $user->id)->first();
                    if ($enseignant && $enseignant->session_id) {
                        $request->session()->put('session_id', $enseignant->session_id);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to set current session after login: '.$e->getMessage());
            }

            return redirect()->intended(route('registre.index'));
        }

        return back()->withErrors([
            'email' => 'Identifiants invalides.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
