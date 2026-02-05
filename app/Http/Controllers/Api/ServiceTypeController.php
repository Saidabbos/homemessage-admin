<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\JsonResponse;

class ServiceTypeController extends Controller
{
    /**
     * Get list of active service types
     */
    public function index(): JsonResponse
    {
        $locale = app()->getLocale();

        $serviceTypes = ServiceType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($st) => [
                'id' => $st->id,
                'name' => $st->getTranslation('name', $locale),
                'description' => $st->getTranslation('description', $locale),
                'price' => (float) $st->price,
                'duration' => $st->duration,
                'image' => $st->image ? asset('storage/' . $st->image) : null,
            ]);

        return response()->json([
            'success' => true,
            'data' => $serviceTypes,
        ]);
    }
}
