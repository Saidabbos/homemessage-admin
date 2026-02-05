<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\SlotRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function __construct(
        protected SlotRepository $slotRepository
    ) {}

    /**
     * Get available slots for booking
     */
    public function available(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'master_id' => 'required|exists:masters,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $slots = $this->slotRepository->getAvailableSlotsForDate(
            $validated['master_id'],
            $validated['date']
        );

        return response()->json([
            'success' => true,
            'data' => $slots,
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
        ]);

        $days = $validated['days'] ?? 7;

        $slots = $this->slotRepository->getAvailableSlotsForMaster(
            $validated['master_id'],
            $validated['start_date'],
            $days
        );

        return response()->json([
            'success' => true,
            'data' => $slots,
        ]);
    }
}
