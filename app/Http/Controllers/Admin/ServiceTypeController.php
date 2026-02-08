<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceType\StoreServiceTypeRequest;
use App\Http\Requests\Admin\ServiceType\UpdateServiceTypeRequest;
use App\Models\ServiceType;
use App\Repositories\ServiceTypeRepository;
use App\Services\ServiceTypeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceTypeController extends Controller
{
    public function __construct(
        protected ServiceTypeService $serviceTypeService,
        protected ServiceTypeRepository $serviceTypeRepository
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/ServiceTypes/Index', [
            'serviceTypes' => $this->serviceTypeRepository->getFilteredPaginated($request->all()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/ServiceTypes/Create');
    }

    public function store(StoreServiceTypeRequest $request)
    {
        $this->serviceTypeService->create($request->validated(), $request);

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Xizmat turi muvaffaqiyatli yaratildi');
    }

    public function show(ServiceType $serviceType)
    {
        return Inertia::render('Admin/ServiceTypes/Show', [
            'serviceType' => $serviceType->load('durations'),
        ]);
    }

    public function edit(ServiceType $serviceType)
    {
        return Inertia::render('Admin/ServiceTypes/Edit', [
            'serviceType' => $this->serviceTypeService->getEditData($serviceType),
        ]);
    }

    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType)
    {
        $this->serviceTypeService->update($serviceType, $request->validated(), $request);

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Xizmat turi muvaffaqiyatli yangilandi');
    }

    public function destroy(ServiceType $serviceType)
    {
        $this->serviceTypeService->delete($serviceType);

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Xizmat turi o\'chirildi');
    }
}
