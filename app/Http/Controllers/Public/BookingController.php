<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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
    public function payment(string $groupId)
    {
        $user = auth()->user();

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

        // If all orders already paid, go to success
        $allPaid = $orders->every(fn($o) => $o->payment_status === Order::PAY_PAID);
        if ($allPaid) {
            return redirect()->route('public.booking.success', ['group_id' => $groupId]);
        }

        $locale = app()->getLocale();

        return Inertia::render('Public/BookingPayment', [
            'groupId' => $groupId,
            'orders' => $orders->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'service_name' => $order->serviceType?->getTranslation('name', $locale) ?? '',
                'master_name' => $order->master?->full_name ?? '',
                'master_photo' => $order->master?->photo_url,
                'duration_minutes' => $order->duration?->duration ?? 60,
                'total_amount' => $order->total_amount,
                'booking_date' => $order->booking_date?->format('d.m.Y'),
                'arrival_window' => $order->arrival_window_start
                    ? substr($order->arrival_window_start, 0, 5) . '-' . substr($order->arrival_window_end ?? '', 0, 5)
                    : '',
                'payment_status' => $order->payment_status,
            ]),
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
        $orders = [];

        if ($groupId && auth()->check()) {
            $locale = app()->getLocale();
            $orders = Order::where('booking_group_id', $groupId)
                ->where('customer_id', auth()->id())
                ->with(['serviceType', 'master'])
                ->get()
                ->map(fn($order) => [
                    'order_number' => $order->order_number,
                    'service_name' => $order->serviceType?->getTranslation('name', $locale) ?? '',
                    'master_name' => $order->master?->full_name ?? '',
                    'total_amount' => $order->total_amount,
                    'payment_status' => $order->payment_status,
                ]);
        }

        return Inertia::render('Public/BookingSuccess', [
            'groupId' => $groupId,
            'orders' => $orders,
        ]);
    }
}
