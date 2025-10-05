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
use App\Helpers\ChineseHelper;

class ScheduleController extends Controller
{
    public function index(Competition $competition)
    {
        $schedules = $competition->schedules()
            ->with([
                'heat.competitionEvent.event',
                'heat.grade',
                'heat.lanes.laneAthletes.athlete.klass'
            ])
            ->orderBy('scheduled_at')
            ->get();

        // 按日期分组
        $schedulesByDate = $schedules->groupBy(function ($schedule) {
            return $schedule->scheduled_at->format('Y-m-d');
        })->sortKeys();

        // 获取未安排的heats
        $heatsWithoutSchedule = Heat::whereHas('competitionEvent', function ($q) use ($competition) {
            $q->where('competition_id', $competition->id);
        })
            ->whereNotIn('heats.id', $competition->schedules()->pluck('heat_id'))
            ->with(['competitionEvent.event', 'grade', 'lanes'])
            ->get();

        // 获取所有年级和班级数据（用于格式化日程）
        $grades = $competition->grades()
            ->with(['klasses.athletes'])
            ->orderBy('order')
            ->get();

        // 生成格式化文本
        $formattedText = '';
        if ($schedules->isNotEmpty()) {
            $formattedText = $this->generateFormattedSchedule($competition, $schedules, $schedulesByDate, $grades);
        }

        return view('schedules.index', compact('competition', 'schedules', 'schedulesByDate', 'heatsWithoutSchedule', 'grades', 'formattedText'));
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

        $schedule->update($validated);

        return redirect()
            ->route('competitions.schedules.index', $competition)
            ->with('success', '日程更新成功');
    }

    public function destroy(Competition $competition, Schedule $schedule)
    {
        $schedule->delete();

        return redirect()
            ->route('competitions.schedules.index', $competition)
            ->with('success', '日程已删除');
    }

    public function bulkNew(Competition $competition)
    {
        // 获取所有未安排的heats
        $scheduledHeatIds = $competition->schedules()->pluck('heat_id')->toArray();

        $unscheduledHeats = Heat::whereHas('competitionEvent', function ($q) use ($competition) {
            $q->where('competition_id', $competition->id);
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

        // 按年级、项目、性别分组
        $groupedHeats = $unscheduledHeats->groupBy(function ($heat) {
            $event = $heat->competitionEvent->event;
            return json_encode([
                'grade_id' => $heat->grade_id,
                'grade_name' => $heat->grade->name,
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

        return view('schedules.bulk_new', compact('competition', 'groupedHeats'));
    }

    public function bulkCreate(Competition $competition, Request $request)
    {
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'event_id' => 'required|exists:events,id',
            'gender' => 'required|string',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'venue' => 'nullable|string',
            'notes' => 'nullable|string',
            'avg_time' => 'required|integer|min:1',
        ]);

        // 强制转换avg_time为整数
        $avgTime = (int) $validated['avg_time'];

        // 查找符合条件的未安排heats
        $scheduledHeatIds = $competition->schedules()->pluck('heat_id')->toArray();

        $heats = Heat::whereHas('competitionEvent', function ($q) use ($competition, $validated) {
            $q->where('competition_id', $competition->id)
                ->where('event_id', $validated['event_id']);
        })
            ->where('grade_id', $validated['grade_id'])
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

            $grade = Grade::find($validated['grade_id']);
            $event = Event::find($validated['event_id']);

            return redirect()
                ->route('competitions.schedules.index', $competition)
                ->with('success', "成功为 {$grade->name} {$event->name} ({$validated['gender']}) 添加了 {$createdCount} 个日程");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', '批量添加失败，请检查时间冲突')
                ->withInput();
        }
    }

    public function print(Competition $competition)
    {
        $schedules = $competition->schedules()
            ->with([
                'heat.competitionEvent.event',
                'heat.grade',
                'heat.lanes.laneAthletes.athlete.klass'
            ])
            ->orderBy('scheduled_at')
            ->get();

        // 按日期分组
        $schedulesByDate = $schedules->groupBy(function ($schedule) {
            return $schedule->scheduled_at->format('Y-m-d');
        })->sortKeys();

        // 获取所有年级和班级数据
        $grades = $competition->grades()
            ->with(['klasses' => function ($q) {
                $q->orderBy('name');
            }, 'klasses.athletes' => function ($q) {
                $q->orderBy('number');
            }])
            ->orderBy('order')
            ->get();

        return view('schedules.print', compact('competition', 'schedules', 'schedulesByDate', 'grades'));
    }

    /**
     * 生成格式化的秩序册文本
     */
    private function generateFormattedSchedule($competition, $schedules, $schedulesByDate, $grades): string
    {
        // 如果没有日程，返回空字符串
        if ($schedules->isEmpty()) {
            return '';
        }

        $text = '';


        // 竞赛日程部分
        foreach ($schedulesByDate as $date => $daySchedules) {
            $text .= "竞 赛 日 程\n";
            $text .= Carbon::parse($date)->format('n月j日') . "上午08:30——12：00\n";

            // 分离径赛和田赛
            $trackSchedules = $daySchedules
                ->filter(fn($s) => $s->heat->competitionEvent->event->event_type === 'track')
                ->sortBy('scheduled_at')
                ->values();

            $fieldSchedules = $daySchedules
                ->filter(fn($s) => $s->heat->competitionEvent->event->event_type === 'field')
                ->sortBy('scheduled_at')
                ->values();

            // 径赛
            if ($trackSchedules->count() > 0) {
                $text .= "径        赛\n";

                // 同一年级同一性别的同一项目需要合并显示
                $groupedTrackSchedules = $trackSchedules->groupBy(function ($schedule) {
                    $event = $schedule->heat->competitionEvent->event;
                    $gradeName = $schedule->heat->grade ? $schedule->heat->grade->name : '';
                    $genderText = $event->gender === '男' ? '男子组' : ($event->gender === '女' ? '女子组' : $event->gender . '组');
                    return $gradeName ? "{$gradeName}{$genderText}{$event->name}" : "{$event->name}({$event->gender})";
                });

                $startIndex = 1;
                foreach ($groupedTrackSchedules as $eventName => $groupedSchedules) {
                    $participantCount = $groupedSchedules->sum(fn($s) => $s->heat->lanes->count());
                    $groupCount = $groupedSchedules->count();
                    $takeCount = ceil($participantCount * 0.6);
                    $firstSchedule = $groupedSchedules->first();
                    $text .= ($startIndex++) . "、{$eventName}预决赛  {$participantCount}人{$groupCount}组  取{$takeCount}名  " . $firstSchedule->scheduled_at->format('G:i') . "\n";
                }
            }

            // 田赛
            if ($fieldSchedules->count() > 0) {
                $text .= "田         赛\n";
                foreach ($fieldSchedules as $index => $schedule) {
                    $event = $schedule->heat->competitionEvent->event;
                    $heat = $schedule->heat;
                    $participantCount = $heat->lanes->count();
                    $takeCount = ceil($participantCount * 0.6);
                    $genderText = $event->gender === '男' ? '男子组' : ($event->gender === '女' ? '女子组' : $event->gender . '组');
                    $gradeName = $heat->grade ? $heat->grade->name : '';
                    $eventName = $gradeName ? "{$gradeName}{$genderText}{$event->name}" : "{$event->name}({$event->gender})";

                    $text .= ($index + 1) . "、{$eventName}预决赛  {$participantCount}人  取{$takeCount}名  " . $schedule->scheduled_at->format('G:i') . "\n";
                }
            }
        }
        // 各班级名单
        $text .= "\n各 班 级 名 单\n\n";
        foreach ($grades as $grade) {
            $text .= "{$grade->name}组\n";
            foreach (
                $grade->klasses->sortBy(function ($klass) {
                    // 将班级名的汉字转换为数字用于排序
                    return ChineseHelper::chineseClassNameToNumber($klass->name);
                }) as $klass
            ) {
                $athletes = $klass->athletes->sortBy('number');
                if ($athletes->count() > 0) {
                    $text .= ChineseHelper::classNameToChinese($klass->name) . "\n";
                    $text .= "运动员：\n";
                    $athleteList = $athletes->map(fn($a) => "{$a->number} {$a->name}")->join(' ');
                    $text .= $athleteList . "\n";
                }
            }
        }

        // 竞赛分组
        $text .= "\n竞 赛 分 组\n\n";


        $heatsByGradeGender = $schedules
            ->pluck('heat')
            ->unique('id')
            ->groupBy(function ($heat) {
                $event = $heat->competitionEvent->event;
                return ($heat->grade->name ?? '其他') . '|' . $event->gender . '|' . $event->event_type;
            });

        // 按年级顺序排序分组
        $heatsByGradeGender = $heatsByGradeGender->sortBy(function ($heats, $key) use ($grades) {
            [$gradeName, $gender, $eventType] = explode('|', $key);
            $gradeOrder = $grades->firstWhere('name', $gradeName)?->order ?? 999;
            $genderOrder = $gender === '男' ? 0 : ($gender === '女' ? 1 : 2);
            $eventTypeOrder = $eventType === 'track' ? 0 : 1;
            return [$gradeOrder, $genderOrder, $eventTypeOrder];
        });

        foreach ($heatsByGradeGender as $key => $gradeHeats) {
            [$gradeName, $gender, $eventType] = explode('|', $key);
            $genderText = $gender === '男' ? '男子组' : '女子组';
            $eventTypeText = $eventType === 'track' ? '径赛' : '田赛';

            $text .= "\n{$gradeName} {$genderText} {$eventTypeText}\n";

            $heatsByEvent = $gradeHeats->groupBy(fn($h) => $h->competitionEvent->event->id);

            $startIndex = 1;
            foreach ($heatsByEvent as $eventId => $eventHeats) {
                $event = $eventHeats->first()->competitionEvent->event;
                $eventHeatsSorted = $eventHeats->sortBy('heat_number');
                $totalParticipants = $eventHeatsSorted->sum(fn($h) => $h->lanes->count());
                $totalGroups = $eventHeatsSorted->count();
                $takeCount = ceil($totalParticipants * 0.6);

                $text .=  ($startIndex++) . "、{$event->name}预决赛，{$totalParticipants}人{$totalGroups}组，取{$takeCount}名\n";

                foreach ($eventHeatsSorted as $heat) {
                    $text .= "第" . ChineseHelper::numberToChinese($heat->heat_number) . "组\n";

                    // 道次行
                    $text .= "道次";
                    foreach ($heat->lanes->sortBy('lane_number') as $lane) {
                        $laneNumbers = ['一', '二', '三', '四', '五', '六', '七', '八'];
                        $text .= "\t" . $laneNumbers[$lane->lane_number - 1];
                    }
                    $text .= "\n";

                    // 姓名行
                    $text .= "姓名";
                    foreach ($heat->lanes->sortBy('lane_number') as $lane) {
                        $laneAthlete = $lane->laneAthletes->first();
                        $athlete = $laneAthlete ? $laneAthlete->athlete : null;
                        $text .= "\t" . ($athlete ? $athlete->name : '');
                    }
                    $text .= "\n";

                    // 号码行
                    $text .= "号码";
                    foreach ($heat->lanes->sortBy('lane_number') as $lane) {
                        $laneAthlete = $lane->laneAthletes->first();
                        $athlete = $laneAthlete ? $laneAthlete->athlete : null;
                        $text .= "\t" . ($athlete ? $athlete->number : '');
                    }
                    $text .= "\n";

                    // 班级行
                    $text .= "班级";
                    foreach ($heat->lanes->sortBy('lane_number') as $lane) {
                        $laneAthlete = $lane->laneAthletes->first();
                        $athlete = $laneAthlete ? $laneAthlete->athlete : null;
                        $text .= "\t" . ($athlete && $athlete->klass ? ChineseHelper::classNameToChinese($athlete->klass->name) : '');
                    }
                    $text .= "\n";
                }
            }
        }

        return $text;
    }
}
