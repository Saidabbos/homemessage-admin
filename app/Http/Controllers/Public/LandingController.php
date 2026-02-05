<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\MasterRepository;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __construct(
        protected ServiceTypeRepository $serviceTypeRepository,
        protected MasterRepository $masterRepository,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('Public/Landing', [
            'serviceTypes' => $this->serviceTypeRepository->getActiveForLanding(),
            'masters' => $this->masterRepository->getFeaturedForLanding(4),
            'stats' => [
                'years' => 12,
                'clients' => '5K',
                'rating' => 4.9,
            ],
        ]);
    }
}
