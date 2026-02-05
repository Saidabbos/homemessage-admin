<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    // Actions
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_SLOT_CHANGED = 'slot_changed';
    public const ACTION_PAYMENT_RECEIVED = 'payment_received';
    public const ACTION_NOTE_ADDED = 'note_added';
    public const ACTION_ASSIGNED = 'assigned';
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'comment',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // ==================== RELATIONS ====================

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== SCOPES ====================

    public function scopeForModel($query, string $type, int $id)
    {
        return $query->where('auditable_type', $type)->where('auditable_id', $id);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== HELPERS ====================

    public static function log(
        Model $model,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $comment = null
    ): self {
        return static::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'comment' => $comment,
        ]);
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATED => 'Yaratildi',
            self::ACTION_UPDATED => 'Yangilandi',
            self::ACTION_DELETED => "O'chirildi",
            self::ACTION_STATUS_CHANGED => "Status o'zgartirildi",
            self::ACTION_SLOT_CHANGED => "Vaqt o'zgartirildi",
            self::ACTION_PAYMENT_RECEIVED => "To'lov qabul qilindi",
            self::ACTION_NOTE_ADDED => "Izoh qo'shildi",
            self::ACTION_ASSIGNED => 'Tayinlandi',
            self::ACTION_LOGIN => 'Kirdi',
            self::ACTION_LOGOUT => 'Chiqdi',
            default => $this->action,
        };
    }
}
