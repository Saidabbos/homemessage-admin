<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $masters = Master::with('serviceTypes')->latest()->paginate(10);
        return Inertia::render('Admin/Masters/Index', [
            'masters' => $masters,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $serviceTypes = ServiceType::where('status', true)->get();
        return Inertia::render('Admin/Masters/Create', [
            'serviceTypes' => $serviceTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:masters',
            'email' => 'nullable|email|unique:masters',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'experience_years' => 'required|integer|min:0|max:50',
            'status' => 'boolean',
            'service_types' => 'nullable|array',
            'service_types.*' => 'exists:service_types,id',
            'uz.bio' => 'nullable|string',
            'ru.bio' => 'nullable|string',
            'en.bio' => 'nullable|string',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('masters', 'public');
        }

        $validated['status'] = $request->boolean('status');

        // Create master
        $master = Master::create($validated);

        // Attach service types
        if ($request->has('service_types')) {
            $master->serviceTypes()->attach($request->service_types);
        }

        return redirect()->route('admin.masters.index')
            ->with('success', 'Master muvaffaqiyatli qo\'shildi');
    }

    /**
     * Display the specified resource.
     */
    public function show(Master $master)
    {
        $master->load('serviceTypes');
        return Inertia::render('Admin/Masters/Show', [
            'master' => $master,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Master $master)
    {
        $serviceTypes = ServiceType::where('status', true)->get();
        return Inertia::render('Admin/Masters/Edit', [
            'master' => [
                'id' => $master->id,
                'first_name' => $master->first_name,
                'last_name' => $master->last_name,
                'phone' => $master->phone,
                'email' => $master->email,
                'photo' => $master->photo,
                'photo_url' => $master->photo_url,
                'birth_date' => $master->birth_date?->format('Y-m-d'),
                'gender' => $master->gender,
                'experience_years' => $master->experience_years,
                'rating' => $master->rating,
                'total_orders' => $master->total_orders,
                'status' => $master->status,
                'service_types' => $master->serviceTypes->pluck('id')->toArray(),
                'uz' => ['bio' => $master->getTranslation('bio', 'uz')],
                'ru' => ['bio' => $master->getTranslation('bio', 'ru')],
                'en' => ['bio' => $master->getTranslation('bio', 'en')],
            ],
            'serviceTypes' => $serviceTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Master $master)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:masters,phone,' . $master->id,
            'email' => 'nullable|email|unique:masters,email,' . $master->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'experience_years' => 'required|integer|min:0|max:50',
            'status' => 'boolean',
            'service_types' => 'nullable|array',
            'service_types.*' => 'exists:service_types,id',
            'uz.bio' => 'nullable|string',
            'ru.bio' => 'nullable|string',
            'en.bio' => 'nullable|string',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($master->photo && Storage::disk('public')->exists($master->photo)) {
                Storage::disk('public')->delete($master->photo);
            }
            $validated['photo'] = $request->file('photo')->store('masters', 'public');
        }

        $validated['status'] = $request->boolean('status');

        $master->update($validated);

        // Sync service types
        $master->serviceTypes()->sync($request->service_types ?? []);

        return redirect()->route('admin.masters.index')
            ->with('success', 'Master muvaffaqiyatli yangilandi');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Master $master)
    {
        // Delete photo
        if ($master->photo && Storage::disk('public')->exists($master->photo)) {
            Storage::disk('public')->delete($master->photo);
        }

        $master->delete();

        return redirect()->route('admin.masters.index')
            ->with('success', 'Master o\'chirildi');
    }
}
