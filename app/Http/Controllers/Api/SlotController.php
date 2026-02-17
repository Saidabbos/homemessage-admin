<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetMultiMasterSlotsRequest;
use App\Http\Requests\Api\GetSlotsByDateRequest;
use App\Http\Requests\Api\GetSlotsRequest;
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
    public function available(GetSlotsRequest $request): JsonResponse
    {
        $master = Master::findOrFail($request->master_id);
        $date = Carbon::parse($request->date);
        $duration = $request->duration ?? 60;

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
    public function byDate(GetSlotsByDateRequest $request): JsonResponse
    {
        $master = Master::findOrFail($request->master_id);
        $startDate = Carbon::parse($request->start_date);
        $days = $request->days ?? 7;
        $duration = $request->duration ?? 60;

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
    public function multiMaster(GetMultiMasterSlotsRequest $request): JsonResponse
    {
        $masterIds = array_filter(array_map('intval', explode(',', $request->master_ids)));
        $date = $request->date;
        $duration = $request->duration ?? 60;

        if (empty($masterIds)) {
            return response()->json([
                'success' => true,
                'data' => ['slots' => []],
            ]);
        }

        $dateCarbon = Carbon::parse($date);
        
        // Get all slots for each master (including disabled)
        $allMasterSlots = [];
        foreach ($masterIds as $masterId) {
            $master = Master::find($masterId);
            if (!$master) continue;
            
            $slots = $this->slotCalculationService->getSlotsForMaster($master, $dateCarbon, $duration);
            foreach ($slots as $slot) {
                $start = $slot['start'];
                if (!isset($allMasterSlots[$start])) {
                    $allMasterSlots[$start] = [
                        'start' => $start,
                        'end' => $slot['end'],
                        'label' => $start,
                        'display' => $slot['display'],
                        'window_start' => $slot['window_start'],
                        'window_end' => $slot['window_end'],
                        'available' => true,
                        'disabled' => false,
                        'reason' => null,
                        'masters_available' => [],
                        'masters_disabled' => [],
                    ];
                }
                
                if ($slot['disabled']) {
                    $allMasterSlots[$start]['masters_disabled'][] = $masterId;
                    // If ANY master has this slot disabled, mark it disabled
                    $allMasterSlots[$start]['disabled'] = true;
                    $allMasterSlots[$start]['available'] = false;
                    $allMasterSlots[$start]['reason'] = $slot['reason'];
                } else {
                    $allMasterSlots[$start]['masters_available'][] = $masterId;
                }
            }
        }
        
        // Filter to only include slots that exist for ALL masters
        $masterCount = count($masterIds);
        $result = [];
        foreach ($allMasterSlots as $slot) {
            $totalMasters = count($slot['masters_available']) + count($slot['masters_disabled']);
            if ($totalMasters === $masterCount) {
                // Remove internal tracking arrays before response
                unset($slot['masters_available'], $slot['masters_disabled']);
                $result[] = $slot;
            }
        }
        
        // Sort by start time
        usort($result, fn($a, $b) => strcmp($a['start'], $b['start']));

        return response()->json([
            'success' => true,
            'data' => ['slots' => $result],
        ]);
    }
}
