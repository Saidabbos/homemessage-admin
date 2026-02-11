<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Rating extends Model
{
    // Rating types
    public const TYPE_CLIENT_TO_MASTER = 'client_to_master';
    public const TYPE_MASTER_TO_CLIENT = 'master_to_client';

    protected $fillable = [
        'type',
        'order_id',
        'master_id',
        'customer_id',
        'overall_rating',
        'punctuality_rating',
        'professionalism_rating',
        'cleanliness_rating',
        'feedback',
        'is_public',
        'token',
        'rated_at',
    ];

    protected $casts = [
        'overall_rating' => 'integer',
        'punctuality_rating' => 'integer',
        'professionalism_rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'is_public' => 'boolean',
        'rated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($rating) {
            if (empty($rating->token)) {
                $rating->token = Str::random(64);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Check if rating is completed
     */
    public function isCompleted(): bool
    {
        return $this->rated_at !== null;
    }

    /**
     * Get rating URL for customer
     */
    public function getRatingUrl(): string
    {
        return url("/rate/{$this->token}");
    }

    /**
     * Calculate average rating for a master (from clients)
     */
    public static function getMasterAverage(int $masterId): ?float
    {
        return static::where('master_id', $masterId)
            ->where('type', self::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->avg('overall_rating');
    }

    /**
     * Get rating count for a master
     */
    public static function getMasterRatingCount(int $masterId): int
    {
        return static::where('master_id', $masterId)
            ->where('type', self::TYPE_CLIENT_TO_MASTER)
            ->whereNotNull('rated_at')
            ->count();
    }

    /**
     * Calculate average rating for a customer (from masters)
     */
    public static function getCustomerAverage(int $customerId): ?float
    {
        return static::where('customer_id', $customerId)
            ->where('type', self::TYPE_MASTER_TO_CLIENT)
            ->whereNotNull('rated_at')
            ->avg('overall_rating');
    }

    /**
     * Get rating count for a customer
     */
    public static function getCustomerRatingCount(int $customerId): int
    {
        return static::where('customer_id', $customerId)
            ->where('type', self::TYPE_MASTER_TO_CLIENT)
            ->whereNotNull('rated_at')
            ->count();
    }

    /**
     * Check if this is a client rating master
     */
    public function isClientToMaster(): bool
    {
        return $this->type === self::TYPE_CLIENT_TO_MASTER;
    }

    /**
     * Check if this is a master rating client
     */
    public function isMasterToClient(): bool
    {
        return $this->type === self::TYPE_MASTER_TO_CLIENT;
    }
}
