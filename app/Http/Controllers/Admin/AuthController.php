<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->hasAnyRole(['admin', 'dispatcher'])) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/Auth/Login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email majburiy',
            'email.email' => 'Email noto\'g\'ri',
            'password.required' => 'Parol majburiy',
            'password.min' => 'Parol kamida 6 ta belgidan iborat bo\'lishi kerak',
        ]);

        // Check if user exists and has admin role
        if (Auth::attempt($validated, $request->remember)) {
            $user = Auth::user();

            if (!$user->hasAnyRole(['admin', 'dispatcher'])) {
                Auth::logout();
                return back()->with('error', 'Siz admin hisobiga ega emassiz');
            }

            return redirect()->route('admin.dashboard')
                ->with('success', 'Admin paneliga xush kelibsiz!');
        }

        return back()->with('error', 'Email yoki parol noto\'g\'ri');
    }

    /**
     * Handle admin logout
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login')
            ->with('success', 'Chiqib ketdingiz');
    }
}
