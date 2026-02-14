<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\MasterRepository;
use App\Repositories\TestimonialRepository;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __construct(
        protected ServiceTypeRepository $serviceTypeRepository,
        protected MasterRepository $masterRepository,
        protected TestimonialRepository $testimonialRepository,
    ) {}

    public function __invoke(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('Public/Landing', [
            'serviceTypes' => $this->serviceTypeRepository->getActiveForLanding(),
            'masters' => $this->masterRepository->getFeaturedForLanding(4),
            'testimonials' => $this->testimonialRepository->getActiveForLanding(3),
            'stats' => [
                'years' => 12,
                'clients' => '5K',
                'rating' => 4.9,
            ],
            'hero' => [
                'title' => Setting::get('hero_title', null, $locale),
                'subtitle' => Setting::get('hero_subtitle', null, $locale),
                'badge' => Setting::get('hero_badge'),
                'cta_text' => Setting::get('hero_cta_text'),
                'view_services_text' => Setting::get('hero_view_services_text'),
                'image' => Setting::get('hero_image')
                    ? asset('storage/' . Setting::get('hero_image'))
                    : null,
            ],
        ]);
    }
}
