<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Repositories\MasterRepository;
use App\Repositories\ServiceTypeRepository;
use Inertia\Inertia;
use Inertia\Response;

class MasterController extends Controller
{
    public function __construct(
        protected MasterRepository $masterRepository,
        protected ServiceTypeRepository $serviceTypeRepository,
    ) {}

    /**
     * Display masters list page
     */
    public function index(): Response
    {
        return Inertia::render('Public/Masters/Index', [
            'masters' => $this->masterRepository->getActiveWithDetails(),
            'serviceTypes' => $this->serviceTypeRepository->getActiveForLanding(),
        ]);
    }

    /**
     * Display master detail page
     */
    public function show(Master $master): Response
    {
        if (!$master->status) {
            abort(404);
        }

        $master->load(['serviceTypes', 'oils']);

        return Inertia::render('Public/Masters/Show', [
            'master' => $this->formatMasterForView($master),
        ]);
    }

    /**
     * Format master data for the view
     */
    protected function formatMasterForView(Master $master): array
    {
        return [
            'id' => $master->id,
            'first_name' => $master->first_name,
            'last_name' => $master->last_name,
            'full_name' => $master->full_name,
            'photo_url' => $master->photo_url,
            'bio' => $master->bio,
            'experience_years' => $master->experience_years,
            'service_types' => $master->serviceTypes->map(fn($st) => [
                'id' => $st->id,
                'name' => $st->getTranslation('name', app()->getLocale()),
                'description' => $st->getTranslation('description', app()->getLocale()),
                'price' => (float) $st->price,
                'duration' => $st->duration,
            ]),
            'oils' => $master->oils->map(fn($oil) => [
                'id' => $oil->id,
                'name' => $oil->getTranslation('name', app()->getLocale()),
            ]),
        ];
    }
}
