<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
}
