<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Show customer dashboard
     */
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Customer/Dashboard', [
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'locale' => $user->locale,
                'status' => $user->status,
                'created_at' => $user->created_at->toDateString(),
            ],
        ]);
    }
}
