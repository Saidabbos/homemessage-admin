<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class OrderRepository
{
    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $orders = $this->applyFilters(Order::query(), $filters)
            ->with(['customer', 'master', 'serviceType', 'duration', 'oil'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Add group count for multi-master bookings
        $groupIds = $orders->getCollection()
            ->pluck('booking_group_id')
            ->filter()
            ->unique()
            ->values();

        if ($groupIds->isNotEmpty()) {
            $groupCounts = Order::whereIn('booking_group_id', $groupIds)
                ->selectRaw('booking_group_id, COUNT(*) as count')
                ->groupBy('booking_group_id')
                ->pluck('count', 'booking_group_id');

            $orders->getCollection()->transform(function ($order) use ($groupCounts) {
                $order->group_count = $order->booking_group_id 
                    ? ($groupCounts[$order->booking_group_id] ?? 1)
                    : 1;
                return $order;
            });
        } else {
            $orders->getCollection()->transform(function ($order) {
                $order->group_count = 1;
                return $order;
            });
        }

        return $orders;
    }

    public function getNewOrders(int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->where('status', Order::STATUS_NEW)
            ->with(['customer', 'master', 'serviceType', 'duration'])
            ->latest()
            ->paginate($perPage);
    }

    public function getActiveOrders(): \Illuminate\Database\Eloquent\Collection
    {
        return Order::query()
            ->active()
            ->with(['customer', 'master', 'serviceType', 'duration'])
            ->latest()
            ->get();
    }

    public function getOrdersForDate(string $date): \Illuminate\Database\Eloquent\Collection
    {
        return Order::query()
            ->forDate($date)
            ->with(['customer', 'master', 'serviceType', 'duration'])
            ->orderBy('arrival_window_start')
            ->get();
    }

    public function getOrdersForMaster(int $masterId, ?string $date = null): \Illuminate\Database\Eloquent\Collection
    {
        return Order::query()
            ->forMaster($masterId)
            ->when($date, fn($q) => $q->forDate($date))
            ->with(['customer', 'serviceType', 'duration'])
            ->orderBy('arrival_window_start')
            ->get();
    }

    public function findWithDetails(int $id): ?Order
    {
        $order = Order::query()
            ->with([
                'customer',
                'master',
                'serviceType',
                'duration',
                'oil',
                'confirmedBy',
                'cancelledBy',
                'logs.user',
                'payments',
            ])
            ->find($id);

        // Load group orders if this is a multi-master booking
        if ($order && $order->booking_group_id) {
            $order->group_orders = Order::where('booking_group_id', $order->booking_group_id)
                ->where('id', '!=', $order->id)
                ->with(['master', 'serviceType'])
                ->get();
        }

        return $order;
    }

    public function getStatusCounts(): array
    {
        return Order::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn($q) =>
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%")
                        );
                });
            })
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn($q, $status) => $q->where('payment_status', $status))
            ->when($filters['master_id'] ?? null, fn($q, $id) => $q->where('master_id', $id))
            ->when($filters['date_from'] ?? null, fn($q, $date) => $q->whereDate('booking_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($q, $date) => $q->whereDate('booking_date', '<=', $date));
    }
}
