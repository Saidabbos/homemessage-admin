<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StandardItem\StoreStandardItemRequest;
use App\Http\Requests\Admin\StandardItem\UpdateStandardItemRequest;
use App\Models\StandardItem;
use App\Repositories\StandardItemRepository;
use App\Services\StandardItemService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StandardItemController extends Controller
{
    public function __construct(
        protected StandardItemService $standardItemService,
        protected StandardItemRepository $standardItemRepository
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/StandardItems/Index', [
            'items' => $this->standardItemRepository->getFilteredPaginated($request->all()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/StandardItems/Create');
    }

    public function store(StoreStandardItemRequest $request)
    {
        $this->standardItemService->create($request->validated(), $request);

        return redirect()->route('admin.standard-items.index')
            ->with('success', 'Standart narsa muvaffaqiyatli yaratildi');
    }

    public function show(StandardItem $standardItem)
    {
        return Inertia::render('Admin/StandardItems/Show', [
            'item' => $standardItem,
        ]);
    }

    public function edit(StandardItem $standardItem)
    {
        return Inertia::render('Admin/StandardItems/Edit', [
            'item' => $this->standardItemService->getEditData($standardItem),
        ]);
    }

    public function update(UpdateStandardItemRequest $request, StandardItem $standardItem)
    {
        $this->standardItemService->update($standardItem, $request->validated(), $request);

        return redirect()->route('admin.standard-items.index')
            ->with('success', 'Standart narsa muvaffaqiyatli yangilandi');
    }

    public function destroy(StandardItem $standardItem)
    {
        $this->standardItemService->delete($standardItem);

        return redirect()->route('admin.standard-items.index')
            ->with('success', 'Standart narsa o\'chirildi');
    }
}
