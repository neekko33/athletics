<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CompetitionEvent extends Model
{
    protected $fillable = [
        'competition_id',
        'event_id',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function athleteCompetitionEvents(): HasMany
    {
        return $this->hasMany(AthleteCompetitionEvent::class);
    }

    public function athletes(): HasManyThrough
    {
        return $this->hasManyThrough(Athlete::class, AthleteCompetitionEvent::class, 'competition_event_id', 'id', 'id', 'athlete_id');
    }

    public function heats(): HasMany
    {
        return $this->hasMany(Heat::class);
    }

    public function schedules()
    {
        return $this->hasManyThrough(Schedule::class, Heat::class);
    }

    public function isFieldEvent(): bool
    {
        return $this->event->event_type === 'field';
    }

    public function isTrackEvent(): bool
    {
        return $this->event->event_type === 'track';
    }

    public function isRelay(): bool
    {
        return $this->event->isRelay();
    }
}
