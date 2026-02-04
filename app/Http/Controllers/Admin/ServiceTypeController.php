<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceTypes = ServiceType::paginate(10);
        return Inertia::render('Admin/ServiceTypes/Index', [
            'serviceTypes' => $serviceTypes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/ServiceTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|unique:service_types|alpha-dash',
            'duration' => 'required|integer|min:15|max:480',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'en.name' => 'required|string|max:255',
            'en.description' => 'nullable|string',
            'uz.name' => 'required|string|max:255',
            'uz.description' => 'nullable|string',
            'ru.name' => 'required|string|max:255',
            'ru.description' => 'nullable|string',
        ], [
            'slug.required' => 'Slug majburiy',
            'slug.unique' => 'Bu slug oldin ishlatilgan',
            'duration.required' => 'Davomiyligi majburiy',
            'price.required' => 'Narx majburiy',
            'en.name.required' => 'English nomi majburiy',
            'uz.name.required' => 'Uzbek nomi majburiy',
            'ru.name.required' => 'Russian nomi majburiy',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('service-types', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['status'] = $request->has('status');

        ServiceType::create($validated);

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Xizmat turi muvaffaqiyatli yaratildi');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceType $serviceType)
    {
        return Inertia::render('Admin/ServiceTypes/Show', [
            'serviceType' => $serviceType,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceType $serviceType)
    {
        return Inertia::render('Admin/ServiceTypes/Edit', [
            'serviceType' => [
                'id' => $serviceType->id,
                'slug' => $serviceType->slug,
                'duration' => $serviceType->duration,
                'price' => $serviceType->price,
                'image' => $serviceType->image,
                'image_url' => $serviceType->image_url,
                'status' => $serviceType->status,
                'en' => [
                    'name' => $serviceType->getTranslation('name', 'en'),
                    'description' => $serviceType->getTranslation('description', 'en'),
                ],
                'uz' => [
                    'name' => $serviceType->getTranslation('name', 'uz'),
                    'description' => $serviceType->getTranslation('description', 'uz'),
                ],
                'ru' => [
                    'name' => $serviceType->getTranslation('name', 'ru'),
                    'description' => $serviceType->getTranslation('description', 'ru'),
                ],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceType $serviceType)
    {
        $validated = $request->validate([
            'slug' => 'required|alpha-dash|unique:service_types,slug,' . $serviceType->id,
            'duration' => 'required|integer|min:15|max:480',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'boolean',
            'en.name' => 'required|string|max:255',
            'en.description' => 'nullable|string',
            'uz.name' => 'required|string|max:255',
            'uz.description' => 'nullable|string',
            'ru.name' => 'required|string|max:255',
            'ru.description' => 'nullable|string',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($serviceType->image && Storage::disk('public')->exists($serviceType->image)) {
                Storage::disk('public')->delete($serviceType->image);
            }

            // Upload new image
            $image = $request->file('image');
            $imagePath = $image->store('service-types', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['status'] = $request->has('status');

        $serviceType->update($validated);

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Xizmat turi muvaffaqiyatli yangilandi');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceType $serviceType)
    {
        // Delete image
        if ($serviceType->image && Storage::disk('public')->exists($serviceType->image)) {
            Storage::disk('public')->delete($serviceType->image);
        }

        $serviceType->delete();

        return redirect()->route('admin.service-types.index')
            ->with('success', 'Xizmat turi o\'chirildi');
    }
}
