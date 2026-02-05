<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customer\UpdateCustomerRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected UserRepository $userRepository
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Customers/Index', [
            'customers' => $this->userRepository->getCustomers($request->all()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(User $customer)
    {
        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(User $customer)
    {
        return Inertia::render('Admin/Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, User $customer)
    {
        $this->customerService->update($customer, $request->validated(), $request);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Mijoz muvaffaqiyatli yangilandi');
    }

    public function destroy(User $customer)
    {
        $this->customerService->delete($customer);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Mijoz o\'chirildi');
    }
}
