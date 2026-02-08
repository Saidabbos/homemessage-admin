<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpRateLimit extends Model
{
    protected $fillable = [
        'phone',
        'ip_address',
        'action',
        'blocked_until',
        'attempts_count',
    ];

    protected function casts(): array
    {
        return [
            'blocked_until' => 'datetime',
            'attempts_count' => 'integer',
        ];
    }
}
