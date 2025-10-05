<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'heat_id',
        'scheduled_at',
        'end_at',
        'venue',
        'duration',
        'status',
        'notes',
        'display_order',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function heat(): BelongsTo
    {
        return $this->belongsTo(Heat::class);
    }

    public static function hasConflict($scheduledAt, $duration, $venue, $excludeId = null): bool
    {
        if (!$scheduledAt || !$duration || !$venue) {
            return false;
        }

        $endAt = $scheduledAt->copy()->addMinutes($duration);
        
        $query = self::where('venue', $venue)
            ->where('status', '!=', self::STATUS_CANCELLED)
            ->where(function ($q) use ($scheduledAt, $endAt) {
                $q->where(function ($q2) use ($scheduledAt, $endAt) {
                    $q2->where('scheduled_at', '<', $endAt)
                       ->where('end_at', '>', $scheduledAt);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function isConflicting(): bool
    {
        if (!$this->scheduled_at || !$this->end_at || !$this->venue) {
            return false;
        }

        return self::where('venue', $this->venue)
            ->where('status', '!=', self::STATUS_CANCELLED)
            ->where('id', '!=', $this->id)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('scheduled_at', '<', $this->end_at)
                       ->where('end_at', '>', $this->scheduled_at);
                });
            })
            ->exists();
    }
}
