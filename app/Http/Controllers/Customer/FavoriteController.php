<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FavoriteController extends Controller
{
    /**
     * Display customer's favorite masters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $locale = app()->getLocale();

        $query = $user->favoriteMasters()
            ->where('masters.status', true)
            ->with(['serviceTypes.activeDurations']);

        // Filter by service type
        if ($serviceTypeId = $request->input('service_type')) {
            $query->whereHas('serviceTypes', function ($q) use ($serviceTypeId) {
                $q->where('service_types.id', $serviceTypeId);
            });
        }

        $favorites = $query->get()->map(function ($master) use ($locale) {
            return [
                'id' => $master->id,
                'full_name' => $master->full_name,
                'photo_url' => $master->photo_url,
                'bio' => $master->getTranslation('bio', $locale),
                'experience_years' => $master->experience_years,
                'completed_orders' => $master->completed_orders ?? 0,
                'service_types' => $master->serviceTypes->map(fn($st) => [
                    'id' => $st->id,
                    'name' => $st->getTranslation('name', $locale),
                ]),
                'min_price' => $master->serviceTypes->flatMap(
                    fn($st) => $st->activeDurations->pluck('price')
                )->min(),
                'is_favorite' => true,
            ];
        });

        // Get service types for filter tabs
        $serviceTypes = \App\Models\ServiceType::where('status', true)
            ->orderBy('name')
            ->get()
            ->map(fn($st) => [
                'id' => $st->id,
                'name' => $st->getTranslation('name', $locale),
            ]);

        return Inertia::render('Customer/Favorites/Index', [
            'favorites' => $favorites,
            'serviceTypes' => $serviceTypes,
            'filters' => $request->only(['service_type']),
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Toggle favorite status for a master.
     */
    public function toggle(Request $request, Master $master)
    {
        $user = Auth::user();

        $exists = $user->favoriteMasters()->where('master_id', $master->id)->exists();

        if ($exists) {
            $user->favoriteMasters()->detach($master->id);
            $isFavorite = false;
        } else {
            $user->favoriteMasters()->attach($master->id);
            $isFavorite = true;
        }

        if ($request->wantsJson()) {
            return response()->json(['is_favorite' => $isFavorite]);
        }

        return back();
    }
}
