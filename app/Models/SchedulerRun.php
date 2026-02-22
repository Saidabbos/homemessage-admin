<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulerRun extends Model
{
    protected $fillable = [
        'command',
        'status',
        'records_processed',
        'details',
        'error',
        'started_at',
        'finished_at',
        'duration_ms',
    ];

    protected $casts = [
        'details' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->duration_ms) {
            return null;
        }

        if ($this->duration_ms < 1000) {
            return $this->duration_ms . 'ms';
        }

        return round($this->duration_ms / 1000, 2) . 's';
    }
}
