<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Heat extends Model
{
    protected $fillable = [
        'competition_event_id',
        'grade_id',
        'heat_number',
        'total_lanes',
    ];

    public function competitionEvent(): BelongsTo
    {
        return $this->belongsTo(CompetitionEvent::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function lanes(): HasMany
    {
        return $this->hasMany(Lane::class);
    }

    public function athletes(): HasManyThrough
    {
        return $this->hasManyThrough(Athlete::class, Lane::class, 'heat_id', 'id', 'id', 'id')
            ->join('lane_athletes', 'lanes.id', '=', 'lane_athletes.lane_id')
            ->where('lane_athletes.athlete_id', '=', 'athletes.id');
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(Schedule::class);
    }

    public function isFieldEvent(): bool
    {
        return $this->competitionEvent->event->event_type === 'field';
    }

    public function isTrackEvent(): bool
    {
        return $this->competitionEvent->event->event_type === 'track';
    }

    public function getNameAttribute(): string
    {
        if ($this->isFieldEvent() && $this->grade) {
            return "{$this->grade->name} - 第{$this->heat_number}组";
        }
        return "第{$this->heat_number}组";
    }
}
