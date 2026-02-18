<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Master extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'telegram_id',
        'telegram_username',
        'notify_telegram',
        'notify_sms',
        'email',
        'photo',
        'bio',
        'birth_date',
        'gender',
        'experience_years',
        'shift_start',
        'shift_end',
        'pressure_levels',
        'status',
        'token',
    ];

    public $translatable = ['bio'];

    protected $appends = ['photo_url', 'full_name', 'name'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'experience_years' => 'integer',
            'pressure_levels' => 'array',
            'status' => 'boolean',
            'notify_telegram' => 'boolean',
            'notify_sms' => 'boolean',
        ];
    }

    /**
     * Check if master has Telegram connected
     */
    public function hasTelegram(): bool
    {
        return !empty($this->telegram_id);
    }

    /**
     * Ish vaqti boshlanishi (default: 08:00)
     */
    public function getShiftStartAttribute($value): string
    {
        return $value ?? '08:00';
    }

    /**
     * Ish vaqti tugashi (default: 22:00)
     */
    public function getShiftEndAttribute($value): string
    {
        return $value ?? '22:00';
    }

    /**
     * Master berilgan pressure levelni qo'llab-quvvatlaydimi?
     */
    public function supportsPressureLevel(string $level): bool
    {
        // Agar pressure_levels null bo'lsa, barcha levellarni qo'llaydi
        if (empty($this->pressure_levels)) {
            return true;
        }

        return in_array($level, $this->pressure_levels);
    }

    /**
     * Get the master's full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Alias for full_name
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get the photo URL
     */
    public function getPhotoUrlAttribute(): string
    {
        if (!$this->photo) {
            return asset('images/master-placeholder.svg');
        }

        // Check if it's a direct public path (starts with /)
        if (str_starts_with($this->photo, '/')) {
            return asset(ltrim($this->photo, '/'));
        }

        // Check storage path
        if (file_exists(public_path('storage/' . $this->photo))) {
            return asset('storage/' . $this->photo);
        }

        return asset('images/master-placeholder.svg');
    }

    /**
     * Get the user account for the master
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the services that the master provides
     */
    public function serviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'master_service_type');
    }

    /**
     * Get the oils that the master brings
     */
    public function oils(): BelongsToMany
    {
        return $this->belongsToMany(Oil::class, 'master_oil');
    }

    /**
     * Get the orders assigned to this master
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the pressure levels this master supports
     */
    public function pressureLevels(): BelongsToMany
    {
        return $this->belongsToMany(PressureLevel::class, 'master_pressure_level');
    }

    /**
     * Get all ratings received from customers
     */
    public function receivedRatings(): HasMany
    {
        return $this->hasMany(Rating::class)->where('type', Rating::TYPE_CLIENT_TO_MASTER);
    }
}
