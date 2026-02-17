<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Show profile edit form
     */
    public function edit()
    {
        $user = Auth::user();

        return Inertia::render('Customer/Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'locale' => $user->locale ?? 'uz',
                'has_pin' => $user->hasPinCode(),
                'pin_set_at' => $user->pin_set_at?->format('d.m.Y'),
            ],
        ]);
    }

    /**
     * Update profile
     */
    public function update(\App\Http\Requests\Customer\UpdateProfileRequest $request)
    {
        $user = Auth::user();

        $user->update([
            'name' => $request->name,
            'email' => $request->email ?? $user->email,
            'locale' => $request->locale ?? $user->locale,
        ]);

        return redirect()->back()->with('success', __('profile.updated'));
    }

    /**
     * Set or update PIN code
     */
    public function setPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:4|regex:/^[0-9]+$/',
            'current_pin' => 'nullable|string|size:4',
        ]);

        $user = Auth::user();
        $newPin = $request->input('pin');
        $currentPin = $request->input('current_pin');

        // If user already has PIN, verify current PIN first
        if ($user->hasPinCode()) {
            if (!$currentPin) {
                return response()->json([
                    'success' => false,
                    'error' => 'current_pin_required',
                    'message' => 'Joriy PIN kodni kiriting',
                ], 400);
            }
            
            if (!$user->verifyPinCode($currentPin)) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_current_pin',
                    'message' => 'Joriy PIN kod noto\'g\'ri',
                ], 401);
            }
        }

        $user->setPinCode($newPin);

        Log::info('Customer: PIN code set', [
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN kod o\'rnatildi',
        ]);
    }

    /**
     * Remove PIN code
     */
    public function removePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|string|size:4',
        ]);

        $user = Auth::user();

        if (!$user->hasPinCode()) {
            return response()->json([
                'success' => false,
                'error' => 'no_pin',
                'message' => 'PIN kod o\'rnatilmagan',
            ], 400);
        }

        if (!$user->verifyPinCode($request->input('current_pin'))) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_pin',
                'message' => 'PIN kod noto\'g\'ri',
            ], 401);
        }

        $user->update([
            'pin_code' => null,
            'pin_set_at' => null,
        ]);

        Log::info('Customer: PIN code removed', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'PIN kod o\'chirildi',
        ]);
    }
}
