<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Oil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class OilController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $oils = Oil::paginate(10);
        return Inertia::render('Admin/Oils/Index', [
            'oils' => $oils,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Oils/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|unique:oils|alpha_dash',
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
            'price.required' => 'Narx majburiy',
            'en.name.required' => 'English nomi majburiy',
            'uz.name.required' => 'Uzbek nomi majburiy',
            'ru.name.required' => 'Russian nomi majburiy',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('oils', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['status'] = $request->has('status');

        Oil::create($validated);

        return redirect()->route('admin.oils.index')
            ->with('success', 'Moy muvaffaqiyatli yaratildi');
    }

    /**
     * Display the specified resource.
     */
    public function show(Oil $oil)
    {
        return Inertia::render('Admin/Oils/Show', [
            'oil' => $oil,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Oil $oil)
    {
        return Inertia::render('Admin/Oils/Edit', [
            'oil' => [
                'id' => $oil->id,
                'slug' => $oil->slug,
                'price' => $oil->price,
                'image' => $oil->image,
                'image_url' => $oil->image_url,
                'status' => $oil->status,
                'en' => [
                    'name' => $oil->getTranslation('name', 'en'),
                    'description' => $oil->getTranslation('description', 'en'),
                ],
                'uz' => [
                    'name' => $oil->getTranslation('name', 'uz'),
                    'description' => $oil->getTranslation('description', 'uz'),
                ],
                'ru' => [
                    'name' => $oil->getTranslation('name', 'ru'),
                    'description' => $oil->getTranslation('description', 'ru'),
                ],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Oil $oil)
    {
        $validated = $request->validate([
            'slug' => 'required|alpha_dash|unique:oils,slug,' . $oil->id,
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
            if ($oil->image && Storage::disk('public')->exists($oil->image)) {
                Storage::disk('public')->delete($oil->image);
            }

            // Upload new image
            $image = $request->file('image');
            $imagePath = $image->store('oils', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['status'] = $request->has('status');

        $oil->update($validated);

        return redirect()->route('admin.oils.index')
            ->with('success', 'Moy muvaffaqiyatli yangilandi');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Oil $oil)
    {
        // Delete image
        if ($oil->image && Storage::disk('public')->exists($oil->image)) {
            Storage::disk('public')->delete($oil->image);
        }

        $oil->delete();

        return redirect()->route('admin.oils.index')
            ->with('success', 'Moy o\'chirildi');
    }
}
