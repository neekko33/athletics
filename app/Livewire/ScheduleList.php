<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Heat;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class ScheduleList extends Component
{
    public $schedulesByDate = [];
    public $competition;
    public $heatsWithoutSchedule = [];
    public $eventType;

    public $isEditMode = false;
    public $eventId;
    public $isModalOpen = false;
    public $scheduleTitle;
    public $scheduleStartDate;
    public $scheduleStartTime;
    public $scheduleEndTime;
    public $scheduleAvgTime;
    public $editSchedulesCount = 0;

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

    private function getSchedulesByDate()
    {
        $schedules = $this->competition->schedules()
            ->with([
                'heat.competitionEvent.event',
                'heat.grade',
                'heat.lanes.laneAthletes.athlete.klass'
            ])
            ->whereHas('heat.competitionEvent.event', function ($query) {
                $query->where('event_type', '=', $this->eventType);
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

        $this->schedulesByDate = $schedulesByDate;
    }

    private function getHeatsWithoutSchedule()
    {
        // 获取未安排的heats
        $heatsWithoutSchedule = Heat::whereHas('competitionEvent', function ($q) {
            $q->where('competition_id', $this->competition->id)->whereHas('event', function ($query) {
                $query->where('event_type', '=', $this->eventType);
            });
        })
            ->whereNotIn('heats.id', $this->competition->schedules()->pluck('heat_id'))
            ->with(['competitionEvent.event', 'grade', 'lanes'])
            ->get();

        $heatsWithoutSchedule = $heatsWithoutSchedule->groupBy(function ($heat) {
            $event = $heat->competitionEvent->event;
            $gender = $event->gender;
            return $gender . '子' . $event->name . '预决赛';
        });

        $this->heatsWithoutSchedule = $heatsWithoutSchedule->toArray();
    }

    public function refreshSchedules()
    {
        $this->getSchedulesByDate();
        $this->getHeatsWithoutSchedule();
    }

    public function mount($competition)
    {
        $this->competition = $competition;
        $this->refreshSchedules();
    }

    public function clearTodaySchedules($date, $eventType)
    {
        $carbonDate = Carbon::parse($date);
        $schedulesToDelete = $this->competition->schedules()
            ->whereDate('scheduled_at', $carbonDate)
            ->whereHas('heat.competitionEvent.event', function ($query) use ($eventType) {
                $query->where('event_type', $eventType);
            });
        $schedulesToDelete->delete();
        $this->refreshSchedules();

        $this->dispatch(
            'alert',
            ['type' => 'success', 'message' => '已清除当天所有日程']
        );
    }

    public function deleteSchedule($event_id)
    {
        $schedulesToDelete = $this->competition->schedules()->whereHas('heat.competitionEvent.event', function ($query) use ($event_id) {
            $query->where('id', $event_id);
        });
        $schedulesToDelete->delete();
        $this->refreshSchedules();

        $this->dispatch(
            'alert',
            ['type' => 'success', 'message' => '已清除日程']
        );
    }

    public function editSchedule($event_id)
    {
        $schedules = $this->competition->schedules()->whereHas('heat.competitionEvent.event', function ($query) use ($event_id) {
            $query->where('id', $event_id);
        })->get();
        $schedule = $schedules->first();
        if ($schedule) {
            $this->editSchedulesCount = count($schedules);
            $this->isEditMode = true;
            $this->eventId = $event_id;
            $event = $schedule->heat->competitionEvent->event;
            $this->scheduleTitle = $event->gender . '子' . $event->name . '预决赛';
            $this->scheduleStartDate = $schedule->scheduled_at->format('Y-m-d');
            $this->scheduleStartTime = $schedule->scheduled_at->format('H:i');
            $this->scheduleAvgTime = $event->avg_time;
            $this->getScheduleEndTime($this->scheduleStartTime, $this->editSchedulesCount, $this->scheduleAvgTime);

            $this->isModalOpen = true;
        }
    }

    private function getScheduleEndTime($startTime, $numberOfHeats, $avgTime)
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $totalMinutes = $numberOfHeats * $avgTime;
        $end = $start->copy()->addMinutes($totalMinutes);
        $this->scheduleEndTime = $end->format('H:i');
    }

    private function getLatestScheduleEndTimeByDate($date)
    {
        $carbonDate = Carbon::parse($date);
        $latestSchedule = $this->competition->schedules()
            ->whereHas('heat.competitionEvent.event', function ($query) {
                $query->where('event_type', '=', $this->eventType);
            })
            ->whereDate('scheduled_at', $carbonDate)
            ->orderBy('end_at', 'desc')
            ->first();

        if ($latestSchedule) {
            return $latestSchedule->end_at->format('H:i');
        }

        return null;
    }

    public function updatedScheduleStartDate($value)
    {
        $this->scheduleStartTime = $this->getLatestScheduleEndTimeByDate($value) ?? '08:30';
    }

    public function updatedScheduleStartTime($value)
    {
        if ($this->isEditMode) {
            $this->getScheduleEndTime($value, $this->editSchedulesCount, $this->scheduleAvgTime);
            return;
        }
        $this->getScheduleEndTime($value, count($this->heatsWithoutSchedule[$this->scheduleTitle]), $this->scheduleAvgTime);
    }

    public function openModal($eventName)
    {
        $this->isEditMode = false;
        $this->scheduleTitle = $eventName;
        $this->scheduleStartDate = $this->competition->start_date->format('Y-m-d');
        $this->scheduleStartTime = $this->getLatestScheduleEndTimeByDate($this->scheduleStartDate) ?? '08:30';
        $event = $this->heatsWithoutSchedule[$eventName][0]['competition_event']['event'];
        $this->scheduleAvgTime = $event['avg_time'];
        $this->getScheduleEndTime($this->scheduleStartTime, count($this->heatsWithoutSchedule[$eventName]), $this->scheduleAvgTime);

        $this->isModalOpen = true;
    }

    public function updateSchedule($event_id)
    {
        $schedules = $this->competition->schedules()->whereHas('heat.competitionEvent.event', function ($query) use ($event_id) {
            $query->where('id', $event_id);
        })->get();

        // 解析开始时间
        try {
            $startDateTime = Carbon::parse($this->scheduleStartDate . ' ' . $this->scheduleStartTime);
        } catch (\Exception $e) {
            $this->dispatch(
                'alert',
                ['type' => 'error', 'message' => '无效的开始时间格式']
            );
            return;
        }

        DB::beginTransaction();
        try {
            $currentTime = $startDateTime->copy();
            foreach ($schedules as $schedule) {
                $schedule->scheduled_at = $currentTime->copy();
                $schedule->end_at = $currentTime->copy()->addMinutes($this->scheduleAvgTime);
                $schedule->save();

                $currentTime = $currentTime->copy()->addMinutes($this->scheduleAvgTime);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'alert',
                ['type' => 'error', 'message' => '更新日程失败: ' . $e->getMessage()]
            );
            return;
        }

        $this->isModalOpen = false;
        $this->refreshSchedules();

        $this->dispatch(
            'alert',
            ['type' => 'success', 'message' => '日程更新成功']
        );
    }

    public function saveSchedule()
    {
        // 强制转换avg_time为整数
        $avgTime = (int)$this->scheduleAvgTime;
        $event = $this->heatsWithoutSchedule[$this->scheduleTitle][0]['competition_event']['event'];

        // 查找符合条件的未安排heats
        $scheduledHeatIds = $this->competition->schedules()->pluck('heat_id')->toArray();

        $heats = Heat::whereHas('competitionEvent', function ($q) use ($event) {
            $q->where('competition_id', $this->competition->id)
                ->where('event_id', $event['id']);
        })
            ->orderBy('grade_id')
            ->when(!empty($scheduledHeatIds), function ($q) use ($scheduledHeatIds) {
                $q->whereNotIn('heats.id', $scheduledHeatIds);
            })
            ->orderBy('heat_number')
            ->get();

        // 解析开始时间
        try {
            $startDateTime = Carbon::parse($this->scheduleStartDate . ' ' . $this->scheduleStartTime);
        } catch (\Exception $e) {
            $this->dispatch(
                'alert',
                ['type' => 'error', 'message' => '无效的开始时间格式']
            );
            return;
        }

        $createdCount = 0;
        $currentTime = $startDateTime->copy();

        DB::beginTransaction();
        try {
            foreach ($heats as $heat) {
                Schedule::create([
                    'heat_id' => $heat->id,
                    'scheduled_at' => $currentTime->copy(),
                    'end_at' => $currentTime->copy()->addMinutes($avgTime),
                ]);

                $createdCount++;
                $currentTime = $currentTime->copy()->addMinutes($avgTime);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'alert',
                ['type' => 'error', 'message' => '添加日程失败: ' . $e->getMessage()]
            );
            return;
        }

        $this->isModalOpen = false;
        $this->refreshSchedules();

        $this->dispatch(
            'alert',
            ['type' => 'success', 'message' => '日程添加成功']
        );
    }
}
