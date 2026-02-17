<?php

namespace App\Mappers;

use App\Models\Order;
use Illuminate\Support\Collection;

class OrderMapper
{
    /**
     * Map order for list view (minimal data)
     */
    public static function toListItem(Order $order, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'booking_date' => $order->booking_date?->format('Y-m-d'),
            'booking_date_display' => $order->booking_date?->format('d.m.Y'),
            'booking_date_formatted' => $order->booking_date?->translatedFormat('d M, Y'),
            'arrival_time' => self::formatTime($order->arrival_window_start),
            'arrival_window' => self::formatArrivalWindow($order),
            'total_amount' => (float) $order->total_amount,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            // Nested objects for Vue compatibility
            'master' => $order->master ? [
                'name' => $order->master->full_name,
                'photo_url' => $order->master->photo_url,
            ] : null,
            'service_type' => $order->serviceType ? [
                'name' => $order->serviceType->getTranslation('name', $locale),
            ] : null,
            'duration' => $order->duration ? [
                'minutes' => $order->duration->duration,
            ] : null,
        ];
    }

    /**
     * Map order for detail view (full data)
     */
    public static function toDetail(Order $order, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'booking_date' => $order->booking_date?->format('d.m.Y'),
            'booking_date_display' => $order->booking_date?->translatedFormat('d M, Y'),
            'arrival_window_start' => $order->arrival_window_start,
            'arrival_window_end' => $order->arrival_window_end,
            'arrival_window' => self::formatArrivalWindow($order),
            'status' => $order->status,
            'status_label' => $order->status_label,
            'payment_status' => $order->payment_status,
            'payment_status_label' => $order->payment_status_label,
            'total_amount' => (float) $order->total_amount,
            'people_count' => $order->people_count ?? 1,
            'pressure_level' => $order->pressure_level,
            'can_be_paid' => $order->canBePaid(),
            'can_cancel' => self::canCancel($order),
            'can_pay' => $order->canBePaid(),
            'address' => $order->full_address ?? $order->address,
            'entrance' => $order->entrance,
            'floor' => $order->floor,
            'apartment' => $order->apartment,
            'landmark' => $order->landmark,
            'notes' => $order->notes,
            'created_at' => $order->created_at?->format('d.m.Y H:i'),
            'master' => $order->master ? [
                'id' => $order->master->id,
                'name' => $order->master->full_name,
                'phone' => $order->master->phone,
                'photo_url' => $order->master->photo_url,
            ] : null,
            'service_type' => $order->serviceType ? [
                'id' => $order->serviceType->id,
                'name' => $order->serviceType->getTranslation('name', $locale),
            ] : null,
            'duration' => $order->duration ? [
                'id' => $order->duration->id,
                'minutes' => $order->duration->duration,
                'price' => (float) $order->duration->price,
            ] : null,
            'oil' => $order->oil ? [
                'id' => $order->oil->id,
                'name' => $order->oil->getTranslation('name', $locale),
            ] : null,
            'logs' => $order->relationLoaded('logs') ? $order->logs->map(fn($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'created_at' => $log->created_at->format('d.m.Y H:i'),
            ])->toArray() : [],
            'is_rated' => self::isRatedByCustomer($order),
        ];
    }

    /**
     * Map order for customer detail view (with rating)
     */
    public static function toCustomerDetail(Order $order, ?array $rating = null, bool $canRate = false, ?string $locale = null): array
    {
        $data = self::toDetail($order, $locale);
        $data['can_rate'] = $canRate;
        $data['customer_rating'] = $rating;
        return $data;
    }

    /**
     * Map order for master day view (calendar)
     */
    public static function toMasterDayItem(Order $order, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'time_start' => self::formatTime($order->arrival_window_start),
            'time_end' => self::formatTime($order->arrival_window_end),
            'arrival_window' => self::formatArrivalWindow($order),
            'duration_minutes' => $order->duration?->duration ?? 60,
            'service_name' => $order->serviceType?->getTranslation('name', $locale) ?? '-',
            'customer_name' => $order->customer?->name ?? '-',
            'customer_phone' => $order->customer?->phone,
            'address' => $order->full_address,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
        ];
    }

    /**
     * Map order for master list view
     */
    public static function toMasterListItem(Order $order, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'booking_date' => $order->booking_date?->format('Y-m-d'),
            'booking_date_formatted' => $order->booking_date?->translatedFormat('d M, Y'),
            'arrival_time' => self::formatTime($order->arrival_window_start),
            'arrival_window' => self::formatArrivalWindow($order),
            'service_name' => $order->serviceType?->getTranslation('name', $locale) ?? '-',
            'customer_name' => $order->customer?->name ?? '-',
            'customer_phone' => $order->customer?->phone ?? '-',
            'duration_minutes' => $order->duration?->duration ?? 60,
            'total_amount' => (float) $order->total_amount,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
        ];
    }

    /**
     * Map order for master detail view
     */
    public static function toMasterDetail(Order $order, ?array $customerRating = null, ?array $masterRating = null, bool $canRate = false, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'booking_date_display' => $order->booking_date?->translatedFormat('d M, Y'),
            'arrival_window' => $order->arrival_window_display ?? self::formatArrivalWindow($order),
            'service_type' => $order->serviceType ? [
                'name' => $order->serviceType->getTranslation('name', $locale),
            ] : null,
            'duration' => $order->duration ? [
                'minutes' => $order->duration->duration,
            ] : null,
            'oil' => $order->oil ? [
                'name' => $order->oil->getTranslation('name', $locale),
            ] : null,
            'people_count' => $order->people_count ?? 1,
            'pressure_level' => $order->pressure_level,
            'total_amount' => (float) $order->total_amount,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'address' => $order->full_address ?? $order->address,
            'customer' => $order->customer ? [
                'name' => $order->customer->name,
                'phone' => $order->customer->phone,
            ] : null,
            'customer_rating' => $customerRating,
            'master_rating' => $masterRating,
            'logs' => $order->relationLoaded('logs') ? $order->logs->map(fn($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'created_at' => $log->created_at->format('d.m.Y H:i'),
            ])->toArray() : [],
            'created_at' => $order->created_at?->format('d.m.Y H:i'),
            'can_rate' => $canRate,
        ];
    }

    /**
     * Map order for admin list
     */
    public static function toAdminListItem(Order $order, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'booking_date' => $order->booking_date?->format('Y-m-d'),
            'booking_date_formatted' => $order->booking_date?->translatedFormat('d M, Y'),
            'arrival_window' => self::formatArrivalWindow($order),
            'service_name' => $order->serviceType?->getTranslation('name', $locale) ?? '-',
            'master_name' => $order->master?->full_name ?? '-',
            'customer_name' => $order->customer?->name ?? '-',
            'customer_phone' => $order->customer?->phone,
            'duration_minutes' => $order->duration?->duration ?? 60,
            'total_amount' => (float) $order->total_amount,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'created_at' => $order->created_at?->format('d.m.Y H:i'),
        ];
    }

    /**
     * Map order for payment page
     */
    public static function toPaymentItem(Order $order, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'service_name' => $order->serviceType?->getTranslation('name', $locale) ?? '',
            'master_name' => $order->master?->full_name ?? '',
            'master_photo' => $order->master?->photo_url,
            'duration_minutes' => $order->duration?->duration ?? 60,
            'total_amount' => (float) $order->total_amount,
            'booking_date' => $order->booking_date?->format('d.m.Y'),
            'arrival_window' => self::formatArrivalWindow($order),
            'payment_status' => $order->payment_status,
        ];
    }

    /**
     * Map order for booking success
     */
    public static function toBookingSuccess(Order $order, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return [
            'order_number' => $order->order_number,
            'service_name' => $order->serviceType?->getTranslation('name', $locale) ?? '',
            'master_name' => $order->master?->full_name ?? '',
            'total_amount' => (float) $order->total_amount,
            'payment_status' => $order->payment_status,
        ];
    }

    /**
     * Map collection of orders
     */
    public static function collection(Collection $orders, string $method = 'toListItem', ?string $locale = null): Collection
    {
        return $orders->map(fn($order) => self::$method($order, $locale));
    }

    /**
     * Format time (HH:MM)
     */
    protected static function formatTime(?string $time): ?string
    {
        if (!$time) return null;
        return substr($time, 0, 5);
    }

    /**
     * Format arrival window (HH:MM - HH:MM)
     */
    protected static function formatArrivalWindow(Order $order): ?string
    {
        if (!$order->arrival_window_start) return null;

        $start = self::formatTime($order->arrival_window_start);
        $end = self::formatTime($order->arrival_window_end);

        return $end ? "{$start} - {$end}" : $start;
    }

    /**
     * Check if order can be cancelled
     */
    protected static function canCancel(Order $order): bool
    {
        return in_array($order->status, ['NEW', 'CONFIRMING', 'CONFIRMED', 'WAITING_PAYMENT']);
    }

    /**
     * Check if order has been rated by customer
     */
    protected static function isRatedByCustomer(Order $order): bool
    {
        return \App\Models\Rating::where('order_id', $order->id)
            ->where('type', \App\Models\Rating::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->exists();
    }
}
