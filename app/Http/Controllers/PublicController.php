<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use App\Models\Master;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicController extends Controller
{
    public function landing()
    {
        // Get active service types
        $serviceTypes = ServiceType::where('status', 'active')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'duration' => $service->duration,
                'icon' => $service->icon ?? 'spa',
            ]);

        // Get featured masters
        $masters = Master::where('status', 'active')
            ->take(4)
            ->get()
            ->map(fn($master) => [
                'id' => $master->id,
                'name' => $master->name,
                'bio' => $master->bio,
                'photo' => $master->photo_url,
                'rating' => $master->average_rating ?? 4.9,
            ]);

        return Inertia::render('Public/Landing', [
            'serviceTypes' => $serviceTypes,
            'masters' => $masters,
            'stats' => [
                'years' => 12,
                'clients' => '5k',
                'rating' => 4.9,
            ],
        ]);
    }
}
