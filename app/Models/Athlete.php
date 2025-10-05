<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Athlete extends Model
{
    protected $fillable = [
        'klass_id',
        'name',
        'gender',
        'number',
    ];

    public function klass(): BelongsTo
    {
        return $this->belongsTo(Klass::class);
    }

    public function grade()
    {
        return $this->hasOneThrough(Grade::class, Klass::class, 'id', 'id', 'klass_id', 'grade_id');
    }

    public function laneAthletes(): HasMany
    {
        return $this->hasMany(LaneAthlete::class);
    }

    public function lanes(): BelongsToMany
    {
        return $this->belongsToMany(Lane::class, 'lane_athletes')->withPivot('relay_position')->withTimestamps();
    }

    public function athleteCompetitionEvents(): HasMany
    {
        return $this->hasMany(AthleteCompetitionEvent::class);
    }

    public function events()
    {
        // 通过athleteCompetitionEvents -> competitionEvent -> event
        return $this->hasManyThrough(
            Event::class,
            CompetitionEvent::class,
            'id', // competitionEvent.id
            'id', // event.id
            'id', // athlete.id (local key)
            'event_id' // competitionEvent.event_id
        )->join('athlete_competition_events', function($join) {
            $join->on('competition_events.id', '=', 'athlete_competition_events.competition_event_id')
                 ->where('athlete_competition_events.athlete_id', '=', $this->id ?? 0);
        });
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->klass->full_name . $this->name;
    }

    public function getEventsAttribute()
    {
        // 使用简单的查询获取events
        if (!$this->exists) {
            return collect();
        }
        
        return Event::whereIn('id', function($query) {
            $query->select('competition_events.event_id')
                ->from('athlete_competition_events')
                ->join('competition_events', 'athlete_competition_events.competition_event_id', '=', 'competition_events.id')
                ->where('athlete_competition_events.athlete_id', $this->id);
        })->get();
    }

    public function getEventIdsAttribute(): array
    {
        if (!$this->exists) {
            return [];
        }
        
        return \DB::table('athlete_competition_events')
            ->join('competition_events', 'athlete_competition_events.competition_event_id', '=', 'competition_events.id')
            ->where('athlete_competition_events.athlete_id', $this->id)
            ->pluck('competition_events.event_id')
            ->toArray();
    }
}
