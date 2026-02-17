<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'locale',
        'telegram_id',
        'telegram_username',
        'telegram_first_name',
        'telegram_photo_url',
        'pin_code',
        'pin_set_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'pin_set_at' => 'datetime',
        ];
    }

    /**
     * Check if user has a PIN code set
     */
    public function hasPinCode(): bool
    {
        return !empty($this->pin_code);
    }

    /**
     * Verify PIN code
     */
    public function verifyPinCode(string $pin): bool
    {
        if (!$this->hasPinCode()) {
            return false;
        }
        return \Illuminate\Support\Facades\Hash::check($pin, $this->pin_code);
    }

    /**
     * Set PIN code (will be hashed)
     */
    public function setPinCode(string $pin): void
    {
        $this->update([
            'pin_code' => \Illuminate\Support\Facades\Hash::make($pin),
            'pin_set_at' => now(),
        ]);
    }

    /**
     * Get the master profile for the user
     */
    public function master(): HasOne
    {
        return $this->hasOne(Master::class);
    }

    /**
     * Get the user's favorite masters
     */
    public function favoriteMasters(): BelongsToMany
    {
        return $this->belongsToMany(Master::class, 'favorite_masters')
            ->withTimestamps();
    }

    /**
     * Get the user's audit logs
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the user's saved addresses
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    /**
     * Get the user's default address
     */
    public function defaultAddress()
    {
        return $this->addresses()->default()->first();
    }
}
