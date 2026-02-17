<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $master = $user->master;

        if (!$master) {
            abort(404);
        }

        $master->load(['serviceTypes', 'pressureLevels']);
        $locale = app()->getLocale();

        return Inertia::render('Master/Profile/Index', [
            'master' => [
                'id' => $master->id,
                'name' => $master->full_name,
                'first_name' => $master->first_name,
                'last_name' => $master->last_name,
                'phone' => $master->phone,
                'email' => $master->email,
                'photo_url' => $master->photo_url,
                'bio' => $master->getTranslation('bio', $locale),
                'experience_years' => $master->experience_years,
                'shift_start' => $master->shift_start,
                'shift_end' => $master->shift_end,
                'rating' => $master->rating ? round((float) $master->rating, 1) : null,
                'rating_count' => $master->rating_count ?? 0,
                'services' => $master->serviceTypes->map(fn ($s) => $s->getTranslation('name', $locale)),
                'pressure_levels' => $master->pressureLevels->map(fn ($p) => $p->getTranslation('name', $locale)),
            ],
        ]);
    }

    /**
     * Show edit form
     */
    public function edit()
    {
        $user = Auth::user();
        $master = $user->master;

        if (!$master) {
            abort(404);
        }

        return Inertia::render('Master/Profile/Edit', [
            'master' => [
                'id' => $master->id,
                'first_name' => $master->first_name,
                'last_name' => $master->last_name,
                'phone' => $master->phone,
                'email' => $master->email,
                'photo_url' => $master->photo_url,
                'bio_uz' => $master->getTranslation('bio', 'uz', false) ?? '',
                'bio_ru' => $master->getTranslation('bio', 'ru', false) ?? '',
            ],
            'user' => [
                'id' => $user->id,
                'locale' => $user->locale ?? 'uz',
            ],
        ]);
    }

    /**
     * Update profile
     */
    public function update(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $master = $user->master;

        $validated = $request->validated();

        // Update master profile
        $master->first_name = $validated['first_name'];
        $master->last_name = $validated['last_name'];
        $master->email = $validated['email'] ?? $master->email;

        // Update bio translations
        $master->setTranslation('bio', 'uz', $validated['bio_uz'] ?? '');
        $master->setTranslation('bio', 'ru', $validated['bio_ru'] ?? '');

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($master->photo) {
                Storage::disk('public')->delete($master->photo);
            }

            $path = $request->file('photo')->store('masters', 'public');
            $master->photo = $path;
        }

        $master->save();

        // Update user locale if changed
        if (isset($validated['locale']) && $validated['locale'] !== $user->locale) {
            $user->update(['locale' => $validated['locale']]);
        }

        return redirect()->route('master.profile')->with('success', __('profile.updated'));
    }
}
