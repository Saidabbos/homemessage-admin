<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class OrderRepository
{
    public function getFilteredPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->applyFilters(Order::query(), $filters)
            ->with(['customer', 'master', 'slot', 'serviceType', 'oil'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getNewOrders(int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->where('status', Order::STATUS_NEW)
            ->with(['customer', 'master', 'slot', 'serviceType'])
            ->latest()
            ->paginate($perPage);
    }

    public function getActiveOrders(): \Illuminate\Database\Eloquent\Collection
    {
        return Order::query()
            ->active()
            ->with(['customer', 'master', 'slot', 'serviceType'])
            ->latest()
            ->get();
    }

    public function getOrdersForDate(string $date): \Illuminate\Database\Eloquent\Collection
    {
        return Order::query()
            ->forDate($date)
            ->with(['customer', 'master', 'slot', 'serviceType'])
            ->orderBy('slot_id')
            ->get();
    }

    public function getOrdersForMaster(int $masterId, ?string $date = null): \Illuminate\Database\Eloquent\Collection
    {
        return Order::query()
            ->forMaster($masterId)
            ->when($date, fn($q) => $q->forDate($date))
            ->with(['customer', 'slot', 'serviceType'])
            ->latest()
            ->get();
    }

    public function findWithDetails(int $id): ?Order
    {
        return Order::query()
            ->with([
                'customer',
                'master',
                'slot',
                'serviceType',
                'oil',
                'confirmedBy',
                'cancelledBy',
                'logs.user',
            ])
            ->find($id);
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
