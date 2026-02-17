<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Master\StoreMasterRequest;
use App\Http\Requests\Admin\Master\UpdateMasterRequest;
use App\Mappers\OrderMapper;
use App\Models\Master;
use App\Models\Order;
use App\Repositories\MasterRepository;
use App\Repositories\OilRepository;
use App\Repositories\PressureLevelRepository;
use App\Repositories\ServiceTypeRepository;
use App\Services\MasterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterController extends Controller
{
    public function __construct(
        protected MasterService $masterService,
        protected MasterRepository $masterRepository,
        protected ServiceTypeRepository $serviceTypeRepository,
        protected OilRepository $oilRepository,
        protected PressureLevelRepository $pressureLevelRepository
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Masters/Index', [
            'masters' => $this->masterRepository->getFilteredPaginated($request->all()),
            'serviceTypes' => $this->serviceTypeRepository->getActive(),
            'filters' => $request->only(['search', 'status', 'gender', 'service_type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Masters/Create', [
            'serviceTypes' => $this->serviceTypeRepository->getActive(),
            'oils' => $this->oilRepository->getActive(),
            'pressureLevels' => $this->pressureLevelRepository->getActive(),
        ]);
    }

    public function store(StoreMasterRequest $request)
    {
        $this->masterService->create($request->validated(), $request);

        return redirect()->route('admin.masters.index')
            ->with('success', 'Master muvaffaqiyatli qo\'shildi');
    }

    public function show(Master $master)
    {
        return Inertia::render('Admin/Masters/Show', [
            'master' => $master->load('serviceTypes', 'oils'),
        ]);
    }

    public function edit(Master $master)
    {
        return Inertia::render('Admin/Masters/Edit', [
            'master' => $this->masterService->getEditData($master),
            'serviceTypes' => $this->serviceTypeRepository->getActive(),
            'oils' => $this->oilRepository->getActive(),
            'pressureLevels' => $this->pressureLevelRepository->getActive(),
        ]);
    }

    public function update(UpdateMasterRequest $request, Master $master)
    {
        $this->masterService->update($master, $request->validated(), $request);

        return redirect()->route('admin.masters.index')
            ->with('success', 'Master muvaffaqiyatli yangilandi');
    }

    public function destroy(Master $master)
    {
        $this->masterService->delete($master);

        return redirect()->route('admin.masters.index')
            ->with('success', 'Master o\'chirildi');
    }

    public function schedule(Request $request, Master $master)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get orders for this master in this month
        $orders = Order::where('master_id', $master->id)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->whereNotIn('status', [Order::STATUS_CANCELLED])
            ->with(['serviceType', 'duration', 'customer'])
            ->orderBy('booking_date')
            ->orderBy('arrival_window_start')
            ->get();

        // Group orders by date for calendar markers
        $ordersByDate = $orders->groupBy(function ($order) {
            return Carbon::parse($order->booking_date)->format('Y-m-d');
        })->map(fn($dayOrders) => OrderMapper::collection($dayOrders, 'toMasterDayItem'));

        return Inertia::render('Admin/Masters/Schedule', [
            'master' => $master,
            'month' => $month,
            'ordersByDate' => $ordersByDate,
            'totalOrders' => $orders->count(),
        ]);
    }
}
