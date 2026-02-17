<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mappers\OrderMapper;
use App\Models\Order;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\MasterRepository;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(
        private ServiceTypeRepository $serviceTypeRepository,
        private MasterRepository $masterRepository,
        private PaymentService $paymentService
    ) {}

    /**
     * Display the public booking page.
     */
    public function index()
    {
        $user = auth()->user();

        // Only customers can book
        if ($user && !$user->hasRole('customer')) {
            if ($user->hasRole('master')) {
                return redirect()->route('master.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }

        Log::info('BookingController@index: Loading public booking page');

        $services = $this->serviceTypeRepository->getActiveWithDurations();
        $masters = $this->masterRepository->getActive();

        return Inertia::render('Public/Booking', [
            'services' => $services,
            'masters' => $masters,
            'customer' => auth()->user(),
        ]);
    }

    /**
     * Display the payment page after order creation.
     */
    public function payment(Request $request, string $groupId)
    {
        $user = auth()->user();
        $source = $request->query('source'); // 'miniapp' or null

        if (!$user || !$user->hasRole('customer')) {
            return redirect()->route('public.booking');
        }

        $orders = Order::where('booking_group_id', $groupId)
            ->where('customer_id', $user->id)
            ->with(['serviceType', 'master', 'duration'])
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('public.booking');
        }

        // If all orders already paid, redirect appropriately
        $allPaid = $orders->every(fn($o) => $o->payment_status === Order::PAY_PAID);
        if ($allPaid) {
            if ($source === 'miniapp') {
                return redirect('/app/orders');
            }
            return redirect()->route('public.booking.success', ['group_id' => $groupId]);
        }

        $locale = app()->getLocale();

        return Inertia::render('Public/BookingPayment', [
            'groupId' => $groupId,
            'source' => $source,
            'orders' => OrderMapper::collection($orders, 'toPaymentItem'),
            'totalAmount' => $orders->sum('total_amount'),
            'providers' => $this->paymentService->getAvailableProviders(),
            'paymentEnabled' => $this->paymentService->isEnabled(),
            'mockEnabled' => config('services.payme.mock_enabled', false) || config('services.click.mock_enabled', false),
            'customer' => $user,
        ]);
    }

    /**
     * Display the booking success page.
     */
    public function success(Request $request)
    {
        $groupId = $request->query('group_id');
        $source = $request->query('source');

        // If from miniapp, redirect to miniapp orders
        if ($source === 'miniapp') {
            return redirect('/app/orders');
        }

        $orders = collect([]);

        if ($groupId && auth()->check()) {
            $orders = Order::where('booking_group_id', $groupId)
                ->where('customer_id', auth()->id())
                ->with(['serviceType', 'master'])
                ->get();
        }

        return Inertia::render('Public/BookingSuccess', [
            'groupId' => $groupId,
            'orders' => OrderMapper::collection($orders, 'toBookingSuccess'),
        ]);
    }
}
