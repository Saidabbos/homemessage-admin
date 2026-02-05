<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Repositories\SlotRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function __construct(
        protected SlotRepository $slotRepository
    ) {}

    /**
     * Get list of active masters
     */
    public function index(Request $request): JsonResponse
    {
        $masters = Master::query()
            ->where('status', true)
            ->with(['serviceTypes:id,name,price', 'oils:id,name'])
            ->withCount(['orders as completed_orders_count' => function ($q) {
                $q->where('status', 'COMPLETED');
            }])
            ->when($request->service_type_id, function ($q, $serviceTypeId) {
                $q->whereHas('serviceTypes', fn($q) => $q->where('service_types.id', $serviceTypeId));
            })
            ->orderBy('first_name')
            ->get()
            ->map(fn($master) => $this->formatMaster($master));

        return response()->json([
            'success' => true,
            'data' => $masters,
        ]);
    }

    /**
     * Get single master details
     */
    public function show(Master $master): JsonResponse
    {
        if (!$master->status) {
            return response()->json([
                'success' => false,
                'message' => 'Master not found',
            ], 404);
        }

        $master->load(['serviceTypes:id,name,price,duration', 'oils:id,name']);
        $master->loadCount(['orders as completed_orders_count' => function ($q) {
            $q->where('status', 'COMPLETED');
        }]);

        return response()->json([
            'success' => true,
            'data' => $this->formatMaster($master, true),
        ]);
    }

    /**
     * Get master's available slots for a date range
     */
    public function slots(Request $request, Master $master): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'sometimes|date|after_or_equal:today',
            'days' => 'sometimes|integer|min:1|max:14',
        ]);

        $startDate = $validated['date'] ?? now()->toDateString();
        $days = $validated['days'] ?? 7;

        $slots = $this->slotRepository->getAvailableSlotsForMaster(
            $master->id,
            $startDate,
            $days
        );

        return response()->json([
            'success' => true,
            'data' => $slots,
        ]);
    }

    /**
     * Format master data for API response
     */
    protected function formatMaster(Master $master, bool $detailed = false): array
    {
        $data = [
            'id' => $master->id,
            'first_name' => $master->first_name,
            'last_name' => $master->last_name,
            'full_name' => $master->full_name,
            'photo' => $master->photo ? asset('storage/' . $master->photo) : null,
            'experience_years' => $master->experience_years,
            'completed_orders' => $master->completed_orders_count ?? 0,
            'service_types' => $master->serviceTypes->map(fn($st) => [
                'id' => $st->id,
                'name' => $st->getTranslation('name', app()->getLocale()),
                'price' => (float) $st->price,
            ]),
        ];

        if ($detailed) {
            $data['bio'] = $master->bio;
            $data['oils'] = $master->oils->map(fn($oil) => [
                'id' => $oil->id,
                'name' => $oil->getTranslation('name', app()->getLocale()),
            ]);
        }

        return $data;
    }
}
