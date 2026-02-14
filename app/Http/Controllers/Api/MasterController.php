<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Services\SlotCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function __construct(
        protected SlotCalculationService $slotService
    ) {}

    /**
     * Get list of active masters
     */
    public function index(Request $request): JsonResponse
    {
        $masters = Master::query()
            ->where('status', true)
            ->with(['serviceTypes:id,name', 'oils:id,name'])
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

        $master->load(['serviceTypes:id,name', 'serviceTypes.durations', 'oils:id,name']);
        $master->loadCount(['orders as completed_orders_count' => function ($q) {
            $q->where('status', 'COMPLETED');
        }]);

        return response()->json([
            'success' => true,
            'data' => $this->formatMaster($master, true),
        ]);
    }

    /**
     * GET /api/masters/{master}/slots
     * Get available slots for a specific master on a specific date
     */
    public function slots(Request $request, Master $master): JsonResponse
    {
        // Validate master is active
        if (!$master->status) {
            return response()->json([
                'success' => false,
                'message' => 'Master is not active',
            ], 404);
        }

        // Validate request parameters
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'required|integer|min:30|max:180',
            'people_count' => 'integer|min:1|max:4',
        ]);

        $date = Carbon::parse($validated['date']);
        $duration = (int) $validated['duration'];
        $peopleCount = (int) ($validated['people_count'] ?? 1);

        // Get slots from SlotCalculationService
        $slots = $this->slotService->getSlotsForMaster(
            $master,
            $date,
            $duration,
            $peopleCount
        );

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date->toDateString(),
                'master' => [
                    'id' => $master->id,
                    'name' => $master->full_name,
                    'shift_start' => substr($master->shift_start ?? '08:00', 0, 5),
                    'shift_end' => substr($master->shift_end ?? '22:00', 0, 5),
                ],
                'slots' => $slots,
                'slots_count' => count($slots),
            ],
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
            ]),
            'service_type_ids' => $master->serviceTypes->pluck('id')->toArray(),
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
