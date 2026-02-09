<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Services\SlotCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlotController extends Controller
{
    public function __construct(
        protected SlotCalculationService $slotCalculationService
    ) {}

    /**
     * Get available slots for booking
     */
    public function available(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'master_id' => 'required|exists:masters,id',
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'sometimes|integer|min:30',
        ]);

        $master = Master::findOrFail($validated['master_id']);
        $date = Carbon::parse($validated['date']);
        $duration = $validated['duration'] ?? 60;

        $slots = $this->slotCalculationService->getSlotsForMaster(
            $master,
            $date,
            $duration
        );

        return response()->json([
            'success' => true,
            'data' => ['slots' => $slots],
        ]);
    }

    /**
     * Get slots grouped by date for a date range
     */
    public function byDate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'master_id' => 'required|exists:masters,id',
            'start_date' => 'required|date|after_or_equal:today',
            'days' => 'sometimes|integer|min:1|max:14',
            'duration' => 'sometimes|integer|min:30',
        ]);

        $master = Master::findOrFail($validated['master_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $days = $validated['days'] ?? 7;
        $duration = $validated['duration'] ?? 60;

        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $slots = $this->slotCalculationService->getSlotsForMaster($master, $date, $duration);
            $result[$date->toDateString()] = $slots;
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get slots available for ALL selected masters (multi-master booking)
     */
    public function multiMaster(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'master_ids' => 'required|string', // comma-separated
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'sometimes|integer|min:30',
        ]);

        $masterIds = array_filter(array_map('intval', explode(',', $validated['master_ids'])));
        $date = $validated['date'];
        $duration = $validated['duration'] ?? 60;

        Log::info('SlotController@multiMaster: Finding slots for multiple masters', [
            'master_ids' => $masterIds,
            'date' => $date,
            'duration' => $duration,
        ]);

        if (empty($masterIds)) {
            return response()->json([
                'success' => true,
                'data' => ['slots' => []],
            ]);
        }

        // Get available slots for each master
        $allMasterSlots = [];
        $dateCarbon = Carbon::parse($date);
        foreach ($masterIds as $masterId) {
            $master = Master::find($masterId);
            if (!$master) continue;
            
            $slots = $this->slotCalculationService->getSlotsForMaster($master, $dateCarbon, $duration);
            $allMasterSlots[$masterId] = collect($slots)->pluck('start')->toArray();
        }

        // Find intersection - slots available for ALL masters
        $commonSlots = null;
        foreach ($allMasterSlots as $masterId => $slots) {
            if ($commonSlots === null) {
                $commonSlots = $slots;
            } else {
                $commonSlots = array_intersect($commonSlots, $slots);
            }
        }

        // Format result
        $result = array_map(function ($start) {
            return [
                'start' => $start,
                'label' => $start,
            ];
        }, array_values($commonSlots ?? []));

        Log::info('SlotController@multiMaster: Found common slots', [
            'count' => count($result),
        ]);

        return response()->json([
            'success' => true,
            'data' => ['slots' => $result],
        ]);
    }
}
