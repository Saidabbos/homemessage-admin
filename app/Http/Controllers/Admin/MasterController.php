<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Master\StoreMasterRequest;
use App\Http\Requests\Admin\Master\UpdateMasterRequest;
use App\Models\Master;
use App\Repositories\MasterRepository;
use App\Repositories\OilRepository;
use App\Repositories\ServiceTypeRepository;
use App\Services\MasterService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterController extends Controller
{
    public function __construct(
        protected MasterService $masterService,
        protected MasterRepository $masterRepository,
        protected ServiceTypeRepository $serviceTypeRepository,
        protected OilRepository $oilRepository
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
}
