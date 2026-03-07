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
        $customer->load('restrictedByUser');

        $orders = $customer->orders()
            ->with(['master', 'serviceType'])
            ->latest('booking_date')
            ->paginate(10);

        $ratingsReceived = $customer->ratingsReceived()
            ->with(['master', 'order'])
            ->whereNotNull('rated_at')
            ->latest('rated_at')
            ->get();

        $ratingsGiven = $customer->ratingsGiven()
            ->with(['master', 'order'])
            ->whereNotNull('rated_at')
            ->latest('rated_at')
            ->get();

        $stats = [
            'total_orders' => $customer->orders()->count(),
            'completed_orders' => $customer->orders()->where('status', 'COMPLETED')->count(),
            'cancelled_orders' => $customer->orders()->where('status', 'CANCELLED')->count(),
            'total_spent' => $customer->orders()->where('payment_status', 'PAID')->sum('total_amount'),
            'avg_rating_received' => $ratingsReceived->avg('overall_rating'),
            'avg_rating_given' => $ratingsGiven->avg('overall_rating'),
        ];

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
            'orders' => $orders,
            'ratingsReceived' => $ratingsReceived,
            'ratingsGiven' => $ratingsGiven,
            'stats' => $stats,
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

    public function toggleRestriction(Request $request, User $customer)
    {
        $request->validate([
            'booking_cutoff_hour' => 'nullable|integer|min:0|max:23',
            'restriction_reason' => 'nullable|string|max:500',
        ]);

        $cutoffHour = $request->filled('booking_cutoff_hour')
            ? (int) $request->input('booking_cutoff_hour')
            : null;

        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();

        $this->customerService->updateCutoffHour(
            $customer,
            $cutoffHour,
            $request->input('restriction_reason'),
            $admin->id
        );

        return redirect()->back()
            ->with('success', $cutoffHour !== null ? 'Vaqt cheklovi qo\'yildi' : 'Cheklov olib tashlandi');
    }

    public function updateNotes(Request $request, User $customer)
    {
        $request->validate(['admin_notes' => 'nullable|string|max:2000']);

        $this->customerService->updateNotes($customer, $request->input('admin_notes'));

        return redirect()->back()
            ->with('success', 'Izoh saqlandi');
    }

    public function destroy(User $customer)
    {
        $this->customerService->delete($customer);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Mijoz o\'chirildi');
    }
}
