<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Oil\StoreOilRequest;
use App\Http\Requests\Admin\Oil\UpdateOilRequest;
use App\Models\Oil;
use App\Repositories\OilRepository;
use App\Services\OilService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OilController extends Controller
{
    public function __construct(
        protected OilService $oilService,
        protected OilRepository $oilRepository
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Oils/Index', [
            'oils' => $this->oilRepository->getFilteredPaginated($request->all()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Oils/Create');
    }

    public function store(StoreOilRequest $request)
    {
        $this->oilService->create($request->validated(), $request);

        return redirect()->route('admin.oils.index')
            ->with('success', 'Moy muvaffaqiyatli yaratildi');
    }

    public function show(Oil $oil)
    {
        return Inertia::render('Admin/Oils/Show', [
            'oil' => $oil,
        ]);
    }

    public function edit(Oil $oil)
    {
        return Inertia::render('Admin/Oils/Edit', [
            'oil' => $this->oilService->getEditData($oil),
        ]);
    }

    public function update(UpdateOilRequest $request, Oil $oil)
    {
        $this->oilService->update($oil, $request->validated(), $request);

        return redirect()->route('admin.oils.index')
            ->with('success', 'Moy muvaffaqiyatli yangilandi');
    }

    public function destroy(Oil $oil)
    {
        $this->oilService->delete($oil);

        return redirect()->route('admin.oils.index')
            ->with('success', 'Moy o\'chirildi');
    }
}
