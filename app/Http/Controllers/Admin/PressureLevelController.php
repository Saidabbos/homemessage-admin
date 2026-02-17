<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PressureLevel\StorePressureLevelRequest;
use App\Http\Requests\Admin\PressureLevel\UpdatePressureLevelRequest;
use App\Models\PressureLevel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PressureLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pressureLevels = PressureLevel::query()
            ->when($request->search, fn($q) => $q->where('slug', 'like', "%{$request->search}%")
                ->orWhere('name', 'like', "%{$request->search}%"))
            ->when($request->status !== null && $request->status !== '', fn($q) =>
                $q->where('status', $request->status === 'active'))
            ->orderBy('sort_order')
            ->paginate(10);

        return Inertia::render('Admin/PressureLevels/Index', [
            'pressureLevels' => $pressureLevels,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/PressureLevels/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePressureLevelRequest $request)
    {
        $validated = $request->validated();

        PressureLevel::create([
            'slug' => $validated['slug'],
            'name' => [
                'uz' => $validated['uz.name'],
                'ru' => $validated['ru.name'],
                'en' => $validated['en.name'],
            ],
            'description' => [
                'uz' => $validated['uz.description'] ?? '',
                'ru' => $validated['ru.description'] ?? '',
                'en' => $validated['en.description'] ?? '',
            ],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'] ?? true,
        ]);

        return redirect()->route('admin.pressure-levels.index')
            ->with('success', 'Bosim darajasi muvaffaqiyatli yaratildi');
    }

    /**
     * Display the specified resource.
     */
    public function show(PressureLevel $pressureLevel)
    {
        return Inertia::render('Admin/PressureLevels/Show', [
            'pressureLevel' => $pressureLevel,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PressureLevel $pressureLevel)
    {
        return Inertia::render('Admin/PressureLevels/Edit', [
            'pressureLevel' => $pressureLevel,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePressureLevelRequest $request, PressureLevel $pressureLevel)
    {
        $validated = $request->validated();

        $pressureLevel->update([
            'slug' => $validated['slug'],
            'name' => [
                'uz' => $validated['uz.name'],
                'ru' => $validated['ru.name'],
                'en' => $validated['en.name'],
            ],
            'description' => [
                'uz' => $validated['uz.description'] ?? '',
                'ru' => $validated['ru.description'] ?? '',
                'en' => $validated['en.description'] ?? '',
            ],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'] ?? true,
        ]);

        return redirect()->route('admin.pressure-levels.index')
            ->with('success', 'Bosim darajasi muvaffaqiyatli yangilandi');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PressureLevel $pressureLevel)
    {
        $pressureLevel->delete();

        return redirect()->route('admin.pressure-levels.index')
            ->with('success', 'Bosim darajasi o\'chirildi');
    }
}
