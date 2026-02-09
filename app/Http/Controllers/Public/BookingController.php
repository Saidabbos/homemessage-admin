<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\MasterRepository;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(
        private ServiceTypeRepository $serviceTypeRepository,
        private MasterRepository $masterRepository
    ) {}

    /**
     * Display the public booking page.
     */
    public function index()
    {
        Log::info('BookingController@index: Loading public booking page');

        $services = $this->serviceTypeRepository->getActiveWithDurations();
        $masters = $this->masterRepository->getActive();

        Log::info('BookingController@index: Loaded data', [
            'services_count' => count($services),
            'masters_count' => count($masters),
        ]);

        return Inertia::render('Public/Booking', [
            'services' => $services,
            'masters' => $masters,
            'customer' => auth()->user(),
        ]);
    }

    /**
     * Display the booking success page.
     */
    public function success()
    {
        Log::info('BookingController@success: Showing booking success page');
        
        return Inertia::render('Public/BookingSuccess');
    }
}
