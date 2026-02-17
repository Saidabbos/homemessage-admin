<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Order;
use App\Models\ServiceType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Reports index page
     */
    public function index(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'master_id', 'service_type_id', 'status', 'payment_status']);
        
        // Default to current month
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        // Build query
        $query = Order::query()
            ->with(['master', 'customer', 'serviceType', 'duration'])
            ->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()]);

        if (!empty($filters['master_id'])) {
            $query->where('master_id', $filters['master_id']);
        }
        if (!empty($filters['service_type_id'])) {
            $query->where('service_type_id', $filters['service_type_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Get orders
        $orders = $query->orderBy('created_at', 'desc')->get();

        // Calculate summary
        $summary = [
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', Order::STATUS_COMPLETED)->count(),
            'cancelled_orders' => $orders->where('status', Order::STATUS_CANCELLED)->count(),
            'total_revenue' => $orders->where('payment_status', 'PAID')->sum('total_amount'),
            'paid_orders' => $orders->where('payment_status', 'PAID')->count(),
            'unpaid_orders' => $orders->where('payment_status', 'UNPAID')->count(),
            'avg_order_value' => $orders->where('payment_status', 'PAID')->count() > 0 
                ? $orders->where('payment_status', 'PAID')->avg('total_amount') 
                : 0,
        ];

        // By status breakdown
        $byStatus = $orders->groupBy('status')->map->count();
        
        // By service type
        $byServiceType = $orders->groupBy('service_type_id')
            ->map(function ($items, $key) {
                $first = $items->first();
                return [
                    'name' => $first->serviceType?->name ?? 'Noma\'lum',
                    'count' => $items->count(),
                    'revenue' => $items->where('payment_status', 'PAID')->sum('total_amount'),
                ];
            })->values();

        // By master
        $byMaster = $orders->groupBy('master_id')
            ->map(function ($items, $key) {
                $first = $items->first();
                return [
                    'name' => $first->master?->full_name ?? 'Noma\'lum',
                    'count' => $items->count(),
                    'completed' => $items->where('status', Order::STATUS_COMPLETED)->count(),
                    'revenue' => $items->where('payment_status', 'PAID')->sum('total_amount'),
                ];
            })->sortByDesc('revenue')->values();

        // By day
        $byDay = $orders->groupBy(fn($o) => Carbon::parse($o->created_at)->format('Y-m-d'))
            ->map(function ($items, $date) {
                return [
                    'date' => Carbon::parse($date)->format('d.m.Y'),
                    'count' => $items->count(),
                    'revenue' => $items->where('payment_status', 'PAID')->sum('total_amount'),
                ];
            })->sortKeys()->values();

        // For filters
        $masters = Master::where('status', true)->orderBy('first_name')->get()
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->full_name]);
        $serviceTypes = ServiceType::where('status', true)->orderBy('name->uz')->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);

        return Inertia::render('Admin/Reports/Index', [
            'filters' => array_merge($filters, ['date_from' => $dateFrom, 'date_to' => $dateTo]),
            'summary' => $summary,
            'byStatus' => $byStatus,
            'byServiceType' => $byServiceType,
            'byMaster' => $byMaster,
            'byDay' => $byDay,
            'orders' => $orders->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'created_at' => $o->created_at->format('d.m.Y H:i'),
                'booking_date' => Carbon::parse($o->booking_date)->format('d.m.Y'),
                'customer_name' => $o->customer?->name ?? $o->customer_phone,
                'customer_phone' => $o->customer_phone,
                'master_name' => $o->master?->full_name,
                'service_name' => $o->serviceType?->name,
                'duration' => $o->duration?->duration,
                'total_amount' => $o->total_amount,
                'status' => $o->status,
                'payment_status' => $o->payment_status,
            ]),
            'masters' => $masters,
            'serviceTypes' => $serviceTypes,
            'statuses' => [
                ['value' => 'NEW', 'label' => 'Yangi'],
                ['value' => 'CONFIRMING', 'label' => 'Tasdiqlanmoqda'],
                ['value' => 'CONFIRMED', 'label' => 'Tasdiqlangan'],
                ['value' => 'WAITING_PAYMENT', 'label' => 'To\'lov kutilmoqda'],
                ['value' => 'PAID', 'label' => 'To\'langan'],
                ['value' => 'IN_PROGRESS', 'label' => 'Jarayonda'],
                ['value' => 'COMPLETED', 'label' => 'Tugallangan'],
                ['value' => 'CANCELLED', 'label' => 'Bekor qilingan'],
            ],
            'paymentStatuses' => [
                ['value' => 'PAID', 'label' => 'To\'langan'],
                ['value' => 'UNPAID', 'label' => 'To\'lanmagan'],
                ['value' => 'PENDING', 'label' => 'Kutilmoqda'],
            ],
        ]);
    }

    /**
     * Export orders to CSV
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'master_id', 'service_type_id', 'status', 'payment_status']);
        
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->format('Y-m-d');

        $query = Order::query()
            ->with(['master', 'customer', 'serviceType', 'duration'])
            ->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()]);

        if (!empty($filters['master_id'])) {
            $query->where('master_id', $filters['master_id']);
        }
        if (!empty($filters['service_type_id'])) {
            $query->where('service_type_id', $filters['service_type_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $filename = 'orders_' . $dateFrom . '_to_' . $dateTo . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($handle, [
                'Buyurtma №',
                'Sana',
                'Bron sanasi',
                'Mijoz',
                'Telefon',
                'Master',
                'Xizmat',
                'Davomiylik (min)',
                'Summa',
                'Status',
                'To\'lov',
            ], ';');

            // Data
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->created_at->format('d.m.Y H:i'),
                    Carbon::parse($order->booking_date)->format('d.m.Y'),
                    $order->customer?->name ?? '-',
                    $order->customer_phone,
                    $order->master?->full_name ?? '-',
                    $order->serviceType?->name ?? '-',
                    $order->duration?->duration ?? '-',
                    $order->total_amount,
                    $this->getStatusLabel($order->status),
                    $this->getPaymentLabel($order->payment_status),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Masters report
     */
    public function masters(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->format('Y-m-d'));

        $masters = Master::where('status', true)
            ->withCount(['orders as total_orders' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()]);
            }])
            ->withCount(['orders as completed_orders' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()])
                    ->where('status', Order::STATUS_COMPLETED);
            }])
            ->withCount(['orders as cancelled_orders' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()])
                    ->where('status', Order::STATUS_CANCELLED);
            }])
            ->withSum(['orders as revenue' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, Carbon::parse($dateTo)->endOfDay()])
                    ->where('payment_status', 'PAID');
            }], 'total_amount')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->full_name,
                'photo' => $m->photo,
                'phone' => $m->phone,
                'total_orders' => $m->total_orders,
                'completed_orders' => $m->completed_orders,
                'cancelled_orders' => $m->cancelled_orders,
                'revenue' => $m->revenue ?? 0,
                'rating' => $m->rating ? round($m->rating, 1) : null,
                'completion_rate' => $m->total_orders > 0 
                    ? round($m->completed_orders / $m->total_orders * 100) 
                    : 0,
            ]);

        return Inertia::render('Admin/Reports/Masters', [
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo],
            'masters' => $masters,
            'summary' => [
                'total_masters' => $masters->count(),
                'total_orders' => $masters->sum('total_orders'),
                'total_revenue' => $masters->sum('revenue'),
                'avg_orders' => $masters->count() > 0 ? round($masters->avg('total_orders'), 1) : 0,
            ],
        ]);
    }

    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'NEW' => 'Yangi',
            'CONFIRMING' => 'Tasdiqlanmoqda',
            'CONFIRMED' => 'Tasdiqlangan',
            'WAITING_PAYMENT' => 'To\'lov kutilmoqda',
            'PAID' => 'To\'langan',
            'IN_PROGRESS' => 'Jarayonda',
            'COMPLETED' => 'Tugallangan',
            'CANCELLED' => 'Bekor qilingan',
            default => $status,
        };
    }

    private function getPaymentLabel(string $status): string
    {
        return match($status) {
            'PAID' => 'To\'langan',
            'UNPAID' => 'To\'lanmagan',
            'PENDING' => 'Kutilmoqda',
            default => $status,
        };
    }
}
