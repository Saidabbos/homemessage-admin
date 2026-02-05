<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Dispatcher\StoreDispatcherRequest;
use App\Http\Requests\Admin\Dispatcher\UpdateDispatcherRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\DispatcherService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DispatcherController extends Controller
{
    public function __construct(
        protected DispatcherService $dispatcherService,
        protected UserRepository $userRepository
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Dispatchers/Index', [
            'dispatchers' => $this->userRepository->getDispatchers($request->all()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Dispatchers/Create');
    }

    public function store(StoreDispatcherRequest $request)
    {
        $this->dispatcherService->create($request->validated(), $request);

        return redirect()->route('admin.dispatchers.index')
            ->with('success', 'Dispetcher muvaffaqiyatli qo\'shildi');
    }

    public function show(User $dispatcher)
    {
        return Inertia::render('Admin/Dispatchers/Show', [
            'dispatcher' => $dispatcher,
        ]);
    }

    public function edit(User $dispatcher)
    {
        return Inertia::render('Admin/Dispatchers/Edit', [
            'dispatcher' => $dispatcher,
        ]);
    }

    public function update(UpdateDispatcherRequest $request, User $dispatcher)
    {
        $this->dispatcherService->update($dispatcher, $request->validated(), $request);

        return redirect()->route('admin.dispatchers.index')
            ->with('success', 'Dispetcher muvaffaqiyatli yangilandi');
    }

    public function destroy(User $dispatcher)
    {
        $this->dispatcherService->delete($dispatcher);

        return redirect()->route('admin.dispatchers.index')
            ->with('success', 'Dispetcher o\'chirildi');
    }
}
