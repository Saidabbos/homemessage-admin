<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Repositories\ServiceTypeRepository;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        protected ServiceTypeRepository $serviceTypeRepository
    ) {}

    public function index()
    {
        Log::info('MiniApp: Home page accessed');

        $services = $this->serviceTypeRepository->getActiveWithDurations();

        return Inertia::render('MiniApp/Home', [
            'services' => $services->map(fn ($service) => [
                'id' => $service->id,
                'slug' => $service->slug,
                'name' => $service->name,
                'description' => $service->description,
                'image_url' => $service->image_url,
                'durations' => $service->durations->where('status', true)->map(fn ($d) => [
                    'id' => $d->id,
                    'duration' => $d->duration,
                    'price' => $d->price,
                    'is_default' => $d->is_default,
                ])->values(),
            ]),
        ]);
    }
}
