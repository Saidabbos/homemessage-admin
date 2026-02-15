<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\MasterBlockedTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterBlockedTimeController extends Controller
{
    /**
     * Get blocked times for a master (API)
     */
    public function index(Master $master)
    {
        $blockedTimes = $master->blockedTimes()
            ->where('blocked_date', '>=', now()->toDateString())
            ->orderBy('blocked_date')
            ->orderBy('start_time')
            ->with('creator:id,name')
            ->get()
            ->map(function ($block) {
                return [
                    'id' => $block->id,
                    'blocked_date' => $block->blocked_date->format('Y-m-d'),
                    'blocked_date_display' => $block->blocked_date->format('d.m.Y'),
                    'start_time' => $block->start_time,
                    'end_time' => $block->end_time,
                    'reason' => $block->reason,
                    'is_full_day' => $block->isFullDay(),
                    'display' => $block->display,
                    'created_by' => $block->creator?->name,
                    'created_at' => $block->created_at->format('d.m.Y H:i'),
                ];
            });

        return response()->json([
            'blocked_times' => $blockedTimes,
        ]);
    }

    /**
     * Store a new blocked time
     */
    public function store(Request $request, Master $master)
    {
        $validated = $request->validate([
            'blocked_date' => 'required|date|after_or_equal:today',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:255',
            'is_full_day' => 'boolean',
        ]);

        // Kun bo'yi bloklash
        if ($request->boolean('is_full_day')) {
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        // Mavjud bloklash bilan kesishishni tekshirish
        $exists = MasterBlockedTime::where('master_id', $master->id)
            ->whereDate('blocked_date', $validated['blocked_date'])
            ->exists();

        if ($exists && $request->boolean('is_full_day')) {
            // Kun bo'yi bloklashda mavjud bloklarni o'chirib, yangi yaratamiz
            MasterBlockedTime::where('master_id', $master->id)
                ->whereDate('blocked_date', $validated['blocked_date'])
                ->delete();
        }

        $blockedTime = MasterBlockedTime::create([
            'master_id' => $master->id,
            'blocked_date' => $validated['blocked_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vaqt muvaffaqiyatli bloklandi',
            'blocked_time' => [
                'id' => $blockedTime->id,
                'blocked_date' => $blockedTime->blocked_date->format('Y-m-d'),
                'blocked_date_display' => $blockedTime->blocked_date->format('d.m.Y'),
                'start_time' => $blockedTime->start_time,
                'end_time' => $blockedTime->end_time,
                'reason' => $blockedTime->reason,
                'is_full_day' => $blockedTime->isFullDay(),
                'display' => $blockedTime->display,
            ],
        ]);
    }

    /**
     * Delete a blocked time
     */
    public function destroy(Master $master, MasterBlockedTime $blockedTime)
    {
        // Verify the blocked time belongs to this master
        if ($blockedTime->master_id !== $master->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $blockedTime->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blok muvaffaqiyatli o\'chirildi',
        ]);
    }
}
