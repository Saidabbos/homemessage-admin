<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Profile\UpdatePasswordRequest;
use App\Http\Requests\Admin\Profile\UpdateProfileRequest;
use App\Services\ProfileService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    protected function guard()
    {
        return Auth::guard('admin');
    }

    public function index()
    {
        return Inertia::render('Admin/Profile/Index', [
            'user' => $this->guard()->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $this->profileService->updateProfile($this->guard()->user(), $request->validated());

        return redirect()->route('admin.profile.index')
            ->with('success', 'Profil muvaffaqiyatli yangilandi');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $validated = $request->validated();

        if (!$this->profileService->updatePassword($this->guard()->user(), $validated['current_password'], $validated['password'])) {
            return back()->withErrors([
                'current_password' => 'Joriy parol noto\'g\'ri',
            ]);
        }

        return redirect()->route('admin.profile.index')
            ->with('success', 'Parol muvaffaqiyatli o\'zgartirildi');
    }
}
