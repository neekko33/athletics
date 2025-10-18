<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Schedule;
use App\Models\Heat;
use App\Models\Grade;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Athlete;

class ScheduleController extends Controller
{
    private function groupSchedulesByEventAndGender($schedules)
    {
        // 根据性别和项目分组日程
        $formattedSchedules = $schedules->groupBy(function ($schedule) {
            $event = $schedule->heat->competitionEvent->event;
            $gender = $schedule->heat->competitionEvent->event->gender;
            return $gender . '子 - ' . $event->name;
        });

        // 生成带有项目名称和时间的日程表
        return $formattedSchedules->map(function ($schedule, $key) {
            $event = $schedule->first()->heat->competitionEvent->event;
            $scheduledAt = $schedule->first()->scheduled_at;
            $endAt = $schedule->last()->end_at;
            return [
                'event' => $event,
                'scheduled_at' => $scheduledAt,
                'end_at' => $endAt,
                'schedules' => $schedule,
            ];
        });
    }

    private function generateIndexViewData(Competition $competition, $eventType)
    {
        $schedules = $competition->schedules()
            ->with([
                'heat.competitionEvent.event',
                'heat.grade',
                'heat.lanes.laneAthletes.athlete.klass'
            ])
            ->whereHas('heat.competitionEvent.event', function ($query) use ($eventType) {
                $query->where('event_type', '=', $eventType);
            })
            ->orderBy('scheduled_at')
            ->get();


        // 按日期分组
        $schedulesByDate = $schedules->groupBy(function ($schedule) {
            return $schedule->scheduled_at->format('Y-m-d');
        })->sortKeys();

        $schedulesByDate = $schedulesByDate->map(function ($schedules) {
            return $this->groupSchedulesByEventAndGender($schedules);
        });

        // 获取未安排的heats
        $heatsWithoutSchedule = Heat::whereHas('competitionEvent', function ($q) use ($competition, $eventType) {
            $q->where('competition_id', $competition->id)->whereHas('event', function ($query) use ($eventType) {
                $query->where('event_type', '=', $eventType);
            });
        })
            ->whereNotIn('heats.id', $competition->schedules()->pluck('heat_id'))
            ->with(['competitionEvent.event', 'grade', 'lanes'])
            ->get();

        $heatsWithoutSchedule = $heatsWithoutSchedule->groupBy(function ($heat) {
            $event = $heat->competitionEvent->event;
            $gender = $event->gender;
            return $gender . '子 - ' . $event->name;
        });

        // 获取所有年级和班级数据（用于格式化日程）
        $grades = $competition->grades()
            ->with(['klasses.athletes'])
            ->orderBy('order')
            ->get();

        return compact('competition', 'schedules', 'schedulesByDate', 'heatsWithoutSchedule', 'grades');
    }

    public function index(Competition $competition)
    {

        $viewData = $this->generateIndexViewData($competition, 'track');
        return view('schedules.index', $viewData);
    }


    public function indexField(Competition $competition)
    {
        $viewData = $this->generateIndexViewData($competition, 'field');
        return view('schedules.index_field', $viewData);
    }

    public function create(Competition $competition, Request $request)
    {
        $heatId = $request->get('heat_id');
        $heat = $heatId ? Heat::find($heatId) : null;

        $availableHeats = Heat::whereHas('competitionEvent', function ($q) use ($competition) {
            $q->where('competition_id', $competition->id);
        })
            ->whereNotIn('heats.id', $competition->schedules()->pluck('heat_id'))
            ->with(['competitionEvent.event', 'grade'])
            ->get();

        return view('schedules.create', compact('competition', 'heat', 'availableHeats'));
    }

    public function store(Competition $competition, Request $request)
    {
        $validated = $request->validate([
            'heat_id' => 'required|exists:heats,id',
            'scheduled_at' => 'required|date',
            'end_at' => 'nullable|date|after:scheduled_at',
            'venue' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $schedule = Schedule::create($validated);

        return redirect()
            ->route('competitions.schedules.index', $competition)
            ->with('success', '日程添加成功');
    }

    public function edit(Competition $competition, Schedule $schedule)
    {
        $availableHeats = Heat::whereHas('competitionEvent', function ($q) use ($competition) {
            $q->where('competition_id', $competition->id);
        })
            ->where(function ($q) use ($schedule) {
                $q->where('heats.id', $schedule->heat_id)
                    ->orWhereNotIn('heats.id', Schedule::pluck('heat_id'));
            })
            ->with(['competitionEvent.event', 'grade'])
            ->get();

        return view('schedules.edit', compact('competition', 'schedule', 'availableHeats'));
    }

    public function update(Competition $competition, Schedule $schedule, Request $request)
    {
        $validated = $request->validate([
            'heat_id' => 'required|exists:heats,id',
            'scheduled_at' => 'required|date',
            'end_at' => 'nullable|date|after:scheduled_at',
            'venue' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $eventType = $schedule->heat->competitionEvent->event->event_type;
        $schedule->update($validated);

        return redirect()
            ->route($eventType === 'field' ? 'competitions.schedules.index-field' : 'competitions.schedules.index', $competition)
            ->with('success', '日程更新成功');
    }

    public function destroy(Competition $competition, Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $eventType = Event::find($validated['event_id'])->event_type;

        $schedulesToDelete = $competition->schedules()->whereHas('heat.competitionEvent.event', function ($query) use ($validated) {
            $query->where('id', $validated['event_id']);
        });

        $schedulesToDelete->delete();

        return redirect()
            ->route($eventType === 'field' ? 'competitions.schedules.index-field' : 'competitions.schedules.index', $competition)
            ->with('success', '日程已删除');
    }

    public function destroyAll(Competition $competition, Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|exists:events,event_type',
            'date' => 'required|date',
        ]);

        $schedulesToDelete = $competition->schedules()
            ->whereDate('scheduled_at', $validated['date'])
            ->whereHas('heat.competitionEvent.event', function ($query) use ($validated) {
                $query->where('event_type', $validated['event_type']);
            });

        $schedulesToDelete->delete();

        return redirect()
            ->route($validated['event_type'] === 'field' ? 'competitions.schedules.index-field' : 'competitions.schedules.index', $competition)
            ->with('success', '所有日程已删除');
    }

    public function bulkNew(Request $request, Competition $competition)
    {
        // 获取所有未安排的heats
        $scheduledHeatIds = $competition->schedules()->pluck('heat_id')->toArray();

        $unscheduledHeats = Heat::whereHas('competitionEvent', function ($q) use ($competition) {
            $q->where('competition_id', $competition->id)->whereHas('event', function ($query) {
                $query->where('event_type', '=', request()->get('type', 'track'));
            });
        })
            ->when(!empty($scheduledHeatIds), function ($q) use ($scheduledHeatIds) {
                $q->whereNotIn('heats.id', $scheduledHeatIds);
            })
            ->with(['competitionEvent.event', 'grade'])
            ->join('grades', 'heats.grade_id', '=', 'grades.id')
            ->join('competition_events', 'heats.competition_event_id', '=', 'competition_events.id')
            ->join('events', 'competition_events.event_id', '=', 'events.id')
            ->orderBy('grades.order')
            ->orderBy('events.name')
            ->orderBy('heats.heat_number')
            ->select('heats.*')
            ->get();

        $groupedHeats = $unscheduledHeats->groupBy(function ($heat) {
            $event = $heat->competitionEvent->event;
            return json_encode([
                'event_id' => $event->id,
                'event_name' => $event->name,
                'gender' => $event->gender,
                'avg_time' => $event->avg_time ?? 5,
            ]);
        })->mapWithKeys(function ($heats, $key) {
            $keyData = json_decode($key, true);
            return [$key => [
                'heats' => $heats,
                'data' => $keyData
            ]];
        });

        // 获取最后一个已安排日程的结束时间
        $date = $request->get('date', $competition->start_date->toDateString());
        $lastSchedule = $competition->schedules()
            ->whereHas('heat.competitionEvent.event', function ($query) {
                $query->where('event_type', '=', request()->get('type', 'track'));
            })
            ->whereDate('scheduled_at', $date)
            ->orderBy('end_at', 'desc')
            ->first();

        return view('schedules.bulk_new', compact('competition', 'groupedHeats', 'lastSchedule'));
    }

    public function bulkCreate(Competition $competition, Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'gender' => 'required|string',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'venue' => 'nullable|string',
            'notes' => 'nullable|string',
            'avg_time' => 'required|integer|min:1',
        ]);

        $eventType = Event::find($validated['event_id'])->event_type;

        // 强制转换avg_time为整数
        $avgTime = (int)$validated['avg_time'];

        // 查找符合条件的未安排heats
        $scheduledHeatIds = $competition->schedules()->pluck('heat_id')->toArray();

        $heats = Heat::whereHas('competitionEvent', function ($q) use ($competition, $validated) {
            $q->where('competition_id', $competition->id)
                ->where('event_id', $validated['event_id']);
        })
            ->orderBy('grade_id')
            ->whereHas('competitionEvent.event', function ($q) use ($validated) {
                $q->where('gender', $validated['gender']);
            })
            ->when(!empty($scheduledHeatIds), function ($q) use ($scheduledHeatIds) {
                $q->whereNotIn('heats.id', $scheduledHeatIds);
            })
            ->orderBy('heat_number')
            ->get();

        if ($heats->isEmpty()) {
            return redirect()
                ->route('competitions.schedules.index', $competition)
                ->with('error', '没有可安排的分组');
        }

        // 解析开始时间
        try {
            $startDateTime = Carbon::parse($validated['start_date'] . ' ' . $validated['start_time']);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', '时间格式错误')
                ->withInput();
        }

        $createdCount = 0;
        $currentTime = $startDateTime->copy();

        DB::beginTransaction();
        try {
            foreach ($heats as $heat) {
                // 当添加日程时，检查同一时间段的另一种比赛中是否有重复的运动员
                $otherEventType = $eventType === 'field' ? 'track' : 'field';
                $conflictingSchedules = Schedule::whereHas('heat.competitionEvent.event', function ($q) use ($otherEventType) {
                    $q->where('event_type', $otherEventType);
                })
                    ->where(function ($q) use ($currentTime, $avgTime) {
                        $q->whereBetween('scheduled_at', [$currentTime, $currentTime->copy()->addMinutes($avgTime)])
                            ->orWhereBetween('end_at', [$currentTime, $currentTime->copy()->addMinutes($avgTime)]);
                    })
                    ->get();

                foreach ($conflictingSchedules as $schedule) {
                    $trackAthleteIds = $schedule->heat->lanes->flatMap(function ($lane) {
                        return $lane->laneAthletes->pluck('athlete_id');
                    })->unique()->toArray();

                    $fieldAthleteIds = $heat->lanes->flatMap(function ($lane) {
                        return $lane->laneAthletes->pluck('athlete_id');
                    })->unique()->toArray();

                    if (count(array_intersect($trackAthleteIds, $fieldAthleteIds)) > 0) {
                        $errorAthletes = Athlete::whereIn('id', array_intersect($trackAthleteIds, $fieldAthleteIds))->get();
                        $errorNames = $errorAthletes->map(function ($athlete) {
                            return $athlete->name . ' ';
                        })->join(', ');

                        throw new \Exception("时间冲突，运动员 {$errorNames} 在同一时间段内有多个项目安排");
                    }
                }
                Schedule::create([
                    'heat_id' => $heat->id,
                    'scheduled_at' => $currentTime->copy(),
                    'end_at' => $currentTime->copy()->addMinutes($avgTime),
                    'venue' => $validated['venue'],
                    'notes' => $validated['notes'],
                ]);

                $createdCount++;
                $currentTime = $currentTime->copy()->addMinutes($avgTime);
            }

            DB::commit();

            $event = Event::find($validated['event_id']);

            return redirect()
                ->route($eventType === 'field' ? 'competitions.schedules.index-field' : 'competitions.schedules.index', $competition)
                ->with('success', "成功为 {$event->name} ({$validated['gender']}) 添加了日程");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage() ?? '批量添加日程时出错')
                ->withInput();
        }
    }
}
