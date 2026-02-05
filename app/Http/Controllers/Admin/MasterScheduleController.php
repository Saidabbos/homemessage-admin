<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Slot;
use App\Models\MasterSlotBooking;
use App\Repositories\MasterRepository;
use App\Repositories\SlotRepository;
use App\Services\SlotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterScheduleController extends Controller
{
    public function __construct(
        protected SlotService $slotService,
        protected MasterRepository $masterRepository,
        protected SlotRepository $slotRepository
    ) {}

    public function index(Request $request)
    {
        $masters = $this->masterRepository->getFilteredPaginated([
            'status' => 'active',
            ...$request->all()
        ]);

        return Inertia::render('Admin/Schedule/Index', [
            'masters' => $masters,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Master $master, Request $request)
    {
        $startDate = $request->get('start_date', now()->toDateString());

        return Inertia::render('Admin/Schedule/Show', [
            'master' => $master,
            'startDate' => $startDate,
            'calendar' => $this->slotService->getMasterWeeklyCalendar($master, $startDate),
            'slots' => $this->slotRepository->getActive(),
        ]);
    }

    public function blockSlot(Request $request, Master $master)
    {
        $request->validate([
            'slot_id' => 'required|exists:slots,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $slot = Slot::findOrFail($request->slot_id);
            $this->slotService->blockSlot($master, $slot, $request->date, $request->reason);

            return back()->with('success', 'Slot bloklandi');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function unblockSlot(Request $request, Master $master)
    {
        $request->validate([
            'booking_id' => 'required|exists:master_slot_bookings,id',
        ]);

        try {
            $booking = MasterSlotBooking::findOrFail($request->booking_id);
            $this->slotService->unblockSlot($booking);

            return back()->with('success', 'Slot ochildi');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function blockDay(Request $request, Master $master)
    {
        $request->validate([
            'date' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        $count = $this->slotService->blockDay($master, $request->date, $request->reason);

        return back()->with('success', "{$count} ta slot bloklandi");
    }

    public function unblockDay(Request $request, Master $master)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $count = $this->slotService->unblockDay($master, $request->date);

        return back()->with('success', "{$count} ta slot ochildi");
    }
}
