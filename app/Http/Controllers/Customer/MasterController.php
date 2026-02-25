<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Repositories\MasterRepository;
use App\Repositories\ServiceTypeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MasterController extends Controller
{
    public function __construct(
        protected MasterRepository $masterRepository,
        protected ServiceTypeRepository $serviceTypeRepository,
    ) {}

    /**
     * Display masters list for customer dashboard.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        $masters = $this->masterRepository->getActiveWithDetails();

        // Mark favorites
        $favoriteIds = $user->favoriteMasters()->pluck('master_id')->toArray();
        $masters = $masters->map(function ($master) use ($favoriteIds) {
            $master['is_favorite'] = in_array($master['id'], $favoriteIds);
            return $master;
        });

        return Inertia::render('Customer/Masters/Index', [
            'masters' => $masters,
            'serviceTypes' => $this->serviceTypeRepository->getActiveForLanding(),
            'filters' => $request->only(['service_type', 'search']),
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Display master detail for customer dashboard.
     */
    public function show(Master $master): Response
    {
        if (!$master->status) {
            abort(404);
        }

        $user = Auth::user();
        $locale = app()->getLocale();

        $master->load(['serviceTypes.activeDurations', 'oils']);

        $isFavorite = $user->favoriteMasters()->where('master_id', $master->id)->exists();

        return Inertia::render('Customer/Masters/Show', [
            'master' => [
                'id' => $master->id,
                'first_name' => $master->first_name,
                'last_name' => $master->last_name,
                'full_name' => $master->full_name,
                'photo_url' => $master->photo_url,
                'bio' => $master->getTranslation('bio', $locale),
                'experience_years' => $master->experience_years,
                'completed_orders' => $master->completed_orders ?? 0,
                'is_favorite' => $isFavorite,
                'service_types' => $master->serviceTypes->map(fn($st) => [
                    'id' => $st->id,
                    'name' => $st->getTranslation('name', $locale),
                    'description' => $st->getTranslation('description', $locale),
                    'durations' => $st->activeDurations->map(fn($d) => [
                        'id' => $d->id,
                        'duration' => $d->duration,
                        'price' => (float) $d->price,
                        'formatted_duration' => $d->formatted_duration,
                        'formatted_price' => $d->formatted_price,
                    ]),
                ]),
                'oils' => $master->oils->map(fn($oil) => [
                    'id' => $oil->id,
                    'name' => $oil->getTranslation('name', $locale),
                ]),
            ],
            'customer' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
        ]);
    }
}
