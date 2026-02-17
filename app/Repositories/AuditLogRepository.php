<?php

namespace App\Repositories;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AuditLogRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return AuditLog::class;
    }

    /**
     * Get paginated audit logs with filters
     */
    public function getFilteredPaginated(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->query()
            ->with(['user', 'auditable']);

        // Filter by action
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by auditable type
        if (!empty($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Search in comment
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get available actions for filter dropdown
     */
    public function getDistinctActions(): array
    {
        return AuditLog::distinct()
            ->pluck('action')
            ->map(function ($action) {
                return [
                    'value' => $action,
                    'label' => $this->getActionLabel($action),
                ];
            })
            ->toArray();
    }

    /**
     * Get available auditable types for filter dropdown
     */
    public function getDistinctTypes(): array
    {
        return AuditLog::distinct()
            ->whereNotNull('auditable_type')
            ->pluck('auditable_type')
            ->map(function ($type) {
                return [
                    'value' => $type,
                    'label' => class_basename($type),
                ];
            })
            ->toArray();
    }

    private function getActionLabel(string $action): string
    {
        return match ($action) {
            AuditLog::ACTION_CREATED => 'Yaratildi',
            AuditLog::ACTION_UPDATED => 'Yangilandi',
            AuditLog::ACTION_DELETED => "O'chirildi",
            AuditLog::ACTION_STATUS_CHANGED => "Status o'zgartirildi",
            AuditLog::ACTION_SLOT_CHANGED => "Vaqt o'zgartirildi",
            AuditLog::ACTION_PAYMENT_RECEIVED => "To'lov qabul qilindi",
            AuditLog::ACTION_NOTE_ADDED => "Izoh qo'shildi",
            AuditLog::ACTION_ASSIGNED => 'Tayinlandi',
            AuditLog::ACTION_LOGIN => 'Kirdi',
            AuditLog::ACTION_LOGOUT => 'Chiqdi',
            default => $action,
        };
    }
}
