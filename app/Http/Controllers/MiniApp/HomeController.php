<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Repositories\ServiceTypeRepository;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        protected ServiceTypeRepository $serviceTypeRepository,
        protected OtpService $otpService,
    ) {}

    /**
     * Mini App home - redirect to login if not authenticated
     */
    public function index()
    {
        Log::info('MiniApp: Home page accessed');

        // Show login if not authenticated
        if (!Auth::check()) {
            return redirect()->route('miniapp.login');
        }

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

    /**
     * Mini App login page
     */
    public function login()
    {
        Log::info('MiniApp: Login page accessed');

        // Redirect to home if already authenticated
        if (Auth::check()) {
            return redirect()->route('miniapp.home');
        }

        return Inertia::render('MiniApp/Login');
    }
}
