<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Testimonial\StoreTestimonialRequest;
use App\Http\Requests\Admin\Testimonial\UpdateTestimonialRequest;
use App\Models\Testimonial;
use App\Repositories\TestimonialRepository;
use App\Services\TestimonialService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TestimonialController extends Controller
{
    public function __construct(
        protected TestimonialService $testimonialService,
        protected TestimonialRepository $testimonialRepository
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => $this->testimonialRepository->getFilteredPaginated($request->all()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Testimonials/Create');
    }

    public function store(StoreTestimonialRequest $request)
    {
        $this->testimonialService->create($request->validated(), $request);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Fikr muvaffaqiyatli yaratildi');
    }

    public function show(Testimonial $testimonial)
    {
        return Inertia::render('Admin/Testimonials/Show', [
            'testimonial' => $testimonial,
        ]);
    }

    public function edit(Testimonial $testimonial)
    {
        return Inertia::render('Admin/Testimonials/Edit', [
            'testimonial' => $this->testimonialService->getEditData($testimonial),
        ]);
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial)
    {
        $this->testimonialService->update($testimonial, $request->validated(), $request);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Fikr muvaffaqiyatli yangilandi');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->testimonialService->delete($testimonial);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Fikr o\'chirildi');
    }
}
