<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Get the admin guard instance
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Show admin login form
     */
    public function showLogin()
    {
        if ($this->guard()->check() && $this->guard()->user()->hasAnyRole(['admin', 'dispatcher'])) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Admin/Auth/Login');
    }

    /**
     * Handle admin login
     */
    public function login(LoginRequest $request)
    {
        // Attempt login with admin guard
        if ($this->guard()->attempt($request->validated(), $request->remember)) {
            $user = $this->guard()->user();

            // Check if user has admin or dispatcher role
            if (!$user->hasAnyRole(['admin', 'dispatcher'])) {
                $this->guard()->logout();
                return back()->with('error', 'Siz admin yoki dispetcher hisobiga ega emassiz');
            }

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('success', 'Admin paneliga xush kelibsiz!');
        }

        return back()->with('error', 'Email yoki parol noto\'g\'ri');
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Chiqib ketdingiz');
    }
}
