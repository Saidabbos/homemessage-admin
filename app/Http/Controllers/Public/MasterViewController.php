<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterViewController extends Controller
{
    /**
     * Master Day View - shows master's orders for a specific day
     * URL: /m/{token}/day/{date?}
     */
    public function day(string $token, ?string $date = null)
    {
        $master = Master::where('token', $token)->firstOrFail();

        $viewDate = $date ? Carbon::parse($date) : Carbon::today();

        $orders = Order::with(['serviceType', 'duration', 'oil', 'customer'])
            ->where('master_id', $master->id)
            ->whereDate('booking_date', $viewDate)
            ->whereIn('status', [
                Order::STATUS_CONFIRMED,
                Order::STATUS_IN_PROGRESS,
                Order::STATUS_COMPLETED,
            ])
            ->where('payment_status', Order::PAY_PAID)
            ->orderBy('arrival_window_start')
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'time' => substr($order->arrival_window_start, 0, 5) . '–' . substr($order->arrival_window_end, 0, 5),
                'service' => $order->serviceType?->name ?? 'Noma\'lum',
                'duration' => $order->duration?->duration ?? 60,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'address_short' => $order->address ? mb_substr($order->address, 0, 50) . '...' : '-',
            ]);

        return Inertia::render('Public/MasterDay', [
            'master' => [
                'id' => $master->id,
                'name' => $master->full_name,
                'photo_url' => $master->photo_url,
            ],
            'date' => $viewDate->format('Y-m-d'),
            'date_display' => $viewDate->format('d.m.Y'),
            'day_name' => $this->getDayName($viewDate),
            'orders' => $orders,
            'token' => $token,
        ]);
    }

    /**
     * Public Order View - shows order details for master
     * URL: /o/{order_number}
     */
    public function order(string $orderNumber)
    {
        $order = Order::with(['master', 'serviceType', 'duration', 'oil', 'customer'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Only show paid/confirmed orders
        if (!in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_IN_PROGRESS, Order::STATUS_COMPLETED])) {
            abort(404);
        }

        return Inertia::render('Public/OrderView', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'date' => $order->booking_date?->format('d.m.Y'),
                'time' => substr($order->arrival_window_start, 0, 5) . '–' . substr($order->arrival_window_end, 0, 5),
                'service' => $order->serviceType?->name ?? 'Noma\'lum',
                'oil' => $order->oil?->name ?? null,
                'duration' => $order->duration?->duration ?? 60,
                'master' => $order->master?->full_name,
                'status' => $order->status,
                'status_label' => $order->status_label,
                // Customer info
                'customer_phone' => $order->contact_phone ?? $order->customer?->phone,
                'customer_name' => $order->customer?->name ?? '-',
                'onsite_phone' => $order->conf_onsite_phone ?? '-',
                // Address
                'address' => $order->address ?? '-',
                'entrance' => $order->conf_entrance ?? $order->entrance ?? '-',
                'floor' => $order->conf_floor ?? $order->floor ?? '-',
                'elevator' => $order->conf_elevator,
                'parking' => $order->conf_parking ?? '-',
                'landmark' => $order->conf_landmark ?? $order->landmark ?? '-',
                // Notes
                'constraints' => $order->conf_constraints ?? '-',
                'note_to_master' => $order->conf_note_to_master ?? '-',
                'space_ok' => $order->conf_space_ok,
                'pets' => $order->conf_pets,
                // Payment
                'payment_status' => $order->payment_status,
                'amount' => number_format($order->total_amount, 0, '', ' '),
            ],
            'master_token' => $order->master?->token,
            'back_date' => $order->booking_date?->format('Y-m-d'),
        ]);
    }

    /**
     * Get day name in Uzbek
     */
    protected function getDayName(Carbon $date): string
    {
        $days = [
            'Monday' => 'Dushanba',
            'Tuesday' => 'Seshanba',
            'Wednesday' => 'Chorshanba',
            'Thursday' => 'Payshanba',
            'Friday' => 'Juma',
            'Saturday' => 'Shanba',
            'Sunday' => 'Yakshanba',
        ];
        return $days[$date->format('l')] ?? '';
    }
}
