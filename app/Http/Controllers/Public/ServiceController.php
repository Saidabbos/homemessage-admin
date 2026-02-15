<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Repositories\ServiceTypeRepository;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function __construct(
        protected ServiceTypeRepository $serviceTypeRepository,
    ) {}

    /**
     * Display services list page.
     */
    public function index(): Response
    {
        $serviceTypes = $this->serviceTypeRepository->getActiveWithDurations();

        return Inertia::render('Public/Services/Index', [
            'serviceTypes' => $serviceTypes,
        ]);
    }

    /**
     * Display service detail page.
     */
    public function show(string $slug): Response
    {
        $serviceType = $this->serviceTypeRepository->findBySlugWithDurations($slug);

        if (!$serviceType) {
            abort(404);
        }

        return Inertia::render('Public/Services/Show', [
            'serviceType' => $serviceType,
            'relatedMasters' => $this->serviceTypeRepository->getMastersForService($serviceType->id),
        ]);
    }
}
