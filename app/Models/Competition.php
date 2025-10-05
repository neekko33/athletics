<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Competition extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'daily_start_time',
        'daily_end_time',
        'track_lanes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function competitionEvents(): HasMany
    {
        return $this->hasMany(CompetitionEvent::class);
    }

    public function events(): HasManyThrough
    {
        return $this->hasManyThrough(Event::class, CompetitionEvent::class);
    }

    public function klasses(): HasManyThrough
    {
        return $this->hasManyThrough(Klass::class, Grade::class);
    }

    public function athletes()
    {
        return $this->hasManyThrough(Athlete::class, Klass::class, 'grade_id', 'klass_id', 'id', 'id')
            ->join('grades', 'klasses.grade_id', '=', 'grades.id')
            ->where('grades.competition_id', $this->id);
    }

    public function schedules()
    {
        return $this->hasManyThrough(Schedule::class, Heat::class, 'competition_event_id', 'heat_id', 'id', 'id')
            ->join('competition_events', 'heats.competition_event_id', '=', 'competition_events.id')
            ->where('competition_events.competition_id', $this->id);
    }

    public function getCompetitionDatesAttribute(): array
    {
        if (!$this->start_date || !$this->end_date) {
            return [];
        }
        
        $dates = [];
        $current = $this->start_date->copy();
        while ($current->lte($this->end_date)) {
            $dates[] = $current->copy();
            $current->addDay();
        }
        return $dates;
    }
}
