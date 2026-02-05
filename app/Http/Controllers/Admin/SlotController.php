<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Slot\StoreSlotRequest;
use App\Http\Requests\Admin\Slot\UpdateSlotRequest;
use App\Models\Slot;
use App\Repositories\SlotRepository;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SlotController extends Controller
{
    public function __construct(
        protected SlotService $slotService,
        protected SlotRepository $slotRepository
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Slots/Index', [
            'slots' => $this->slotRepository->getFilteredPaginated($request->all()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Slots/Create');
    }

    public function store(StoreSlotRequest $request)
    {
        $this->slotService->create($request->validated());

        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot muvaffaqiyatli yaratildi');
    }

    public function show(Slot $slot)
    {
        return Inertia::render('Admin/Slots/Show', [
            'slot' => $slot,
        ]);
    }

    public function edit(Slot $slot)
    {
        return Inertia::render('Admin/Slots/Edit', [
            'slot' => $slot,
        ]);
    }

    public function update(UpdateSlotRequest $request, Slot $slot)
    {
        $this->slotService->update($slot, $request->validated());

        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot muvaffaqiyatli yangilandi');
    }

    public function destroy(Slot $slot)
    {
        $this->slotService->delete($slot);

        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot o\'chirildi');
    }

    public function generateDefaults()
    {
        $this->slotService->generateDefaultSlots();

        return redirect()->route('admin.slots.index')
            ->with('success', 'Standart slotlar yaratildi');
    }
}
