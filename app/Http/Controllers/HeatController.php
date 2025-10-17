<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Heat;
use App\Models\Athlete;
use App\Models\LaneAthlete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HeatController extends Controller
{
    public function index(Competition $competition)
    {
        // 获取径赛项目及其分组
        $trackEvents = $competition->competitionEvents()
            ->whereHas('event', function($query) {
                $query->where('event_type', 'track');
            })
            ->with([
                'event',
                'heats' => function($query) {
                    $query->with([
                        'grade',
                        'lanes' => function($q) {
                            $q->with(['laneAthletes.athlete.klass'])->orderBy('lane_number');
                        }
                    ])->orderBy('heat_number');
                },
                'athleteCompetitionEvents'
            ])
            ->get();

        // 获取田赛项目及其分组
        $fieldEvents = $competition->competitionEvents()
            ->whereHas('event', function($query) {
                $query->where('event_type', 'field');
            })
            ->with([
                'event',
                'heats' => function($query) {
                    $query->with([
                        'grade',
                        'lanes' => function($q) {
                            $q->with(['laneAthletes.athlete.klass'])->orderBy('lane_number');
                        }
                    ]);
                },
                'athleteCompetitionEvents'
            ])
            ->get();

        return view('heats.index', compact('competition', 'trackEvents', 'fieldEvents'));
    }

    public function generateAll(Competition $competition)
    {
        // 为所有径赛项目生成分组
        $trackEvents = $competition->competitionEvents()
            ->whereHas('event', function($query) {
                $query->where('event_type', 'track');
            })
            ->with([
                'event',
                'athleteCompetitionEvents.athlete.klass.grade'
            ])
            ->get();

        $generatedCount = 0;
        $errors = [];
        $warnings = [];

        // 定义长距离项目（按年级分组，不分赛道）
        $longDistanceEvents = ['800米', '1000米', '1500米'];

        DB::beginTransaction();
        try {
            foreach ($trackEvents as $competitionEvent) {
                // 清除旧的分组
                $competitionEvent->heats()->delete();

                // 获取所有报名的运动员
                $athletes = $competitionEvent->athleteCompetitionEvents->map->athlete;

                if ($athletes->isEmpty()) {
                    continue;
                }

                // 检查是否是接力项目
                $isRelay = str_contains($competitionEvent->event->name, '接力') ||
                           str_contains($competitionEvent->event->name, '4×100') ||
                           str_contains($competitionEvent->event->name, '4×400') ||
                           str_contains($competitionEvent->event->name, '4*300') ||
                           str_contains($competitionEvent->event->name, '4*400');

                // 检查是否是长距离项目
                $isLongDistance = in_array($competitionEvent->event->name, $longDistanceEvents);

                $maxLanes = $competition->track_lanes;

                if ($isRelay) {
                    // 接力项目：按年级→班级分组，每个班级4人
                    $result = $this->generateRelayHeats($competitionEvent, $athletes, $maxLanes, $warnings);
                    $generatedCount += $result;
                } elseif ($isLongDistance) {
                    // 长距离项目：按年级分组（类似田赛）
                    $result = $this->generateLongDistanceHeats($competitionEvent, $athletes);
                    $generatedCount += $result;
                } else {
                    // 普通径赛项目：按年级分组，每组最多maxLanes人
                    $result = $this->generateTrackHeats($competitionEvent, $athletes, $maxLanes);
                    $generatedCount += $result;
                }
            }

            DB::commit();

            if (!empty($warnings)) {
                return redirect()->route('competitions.heats.index', $competition)
                    ->with('warning', '生成完成，但有以下警告：<br/>' . implode('<br/>', $warnings));
            }

            if ($generatedCount > 0) {
                return redirect()->route('competitions.heats.index', $competition)
                    ->with('success', "成功生成 {$generatedCount} 个比赛分组");
            } else {
                return redirect()->route('competitions.heats.index', $competition)
                    ->with('warning', '未能生成任何分组。' . implode('; ', $errors));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Generate heats failed', ['error' => $e->getMessage()]);

            return redirect()->route('competitions.heats.index', $competition)
                ->with('error', '生成分组失败: ' . $e->getMessage());
        }
    }

    private function generateRelayHeats($competitionEvent, $athletes, $maxLanes, &$warnings)
    {
        $generatedCount = 0;

        // 按年级分组
        $athletesByGrade = $athletes->groupBy(function($athlete) {
            return $athlete->klass->grade_id;
        });

        foreach ($athletesByGrade as $gradeId => $gradeAthletes) {
            $grade = $gradeAthletes->first()->klass->grade;

            // 按班级分组
            $athletesByKlass = $gradeAthletes->groupBy('klass_id');

            $validTeams = [];
            $insufficientTeams = [];

            foreach ($athletesByKlass as $klassId => $klassAthletes) {
                if ($klassAthletes->count() >= 4) {
                    $validTeams[] = [
                        'klass' => $klassAthletes->first()->klass,
                        'athletes' => $klassAthletes
                    ];
                } else {
                    $klass = $klassAthletes->first()->klass;
                    $insufficientTeams[] = "{$grade->name} {$klass->name} 只有{$klassAthletes->count()}人（需要4人）";
                }
            }

            if (!empty($insufficientTeams)) {
                $warnings[] = "{$competitionEvent->event->name} - " . implode('; ', $insufficientTeams);
            }

            // 为有效队伍创建分组
            if (!empty($validTeams)) {
                $heatCount = ceil(count($validTeams) / $maxLanes);

                for ($i = 0; $i < $heatCount; $i++) {
                    $heatTeams = array_slice($validTeams, $i * $maxLanes, $maxLanes);

                    if (empty($heatTeams)) {
                        continue;
                    }

                    $heat = $competitionEvent->heats()->create([
                        'grade_id' => $grade->id,
                        'heat_number' => $i + 1,
                        'total_lanes' => $maxLanes
                    ]);

                    foreach ($heatTeams as $laneIndex => $team) {
                        $lane = $heat->lanes()->create([
                            'lane_number' => $laneIndex + 1
                        ]);

                        // 随机选择4名运动员
                        $selectedAthletes = $team['athletes']->shuffle()->take(4);

                        foreach ($selectedAthletes as $position => $athlete) {
                            $lane->laneAthletes()->create([
                                'athlete_id' => $athlete->id,
                                'relay_position' => $position + 1
                            ]);
                        }
                    }

                    $generatedCount++;
                }
            }
        }

        return $generatedCount;
    }

    private function generateTrackHeats($competitionEvent, $athletes, $maxLanes)
    {
        $generatedCount = 0;

        // 按年级分组
        $athletesByGrade = $athletes->groupBy(function($athlete) {
            return $athlete->klass->grade_id;
        });

        foreach ($athletesByGrade as $gradeId => $gradeAthletes) {
            $grade = $gradeAthletes->first()->klass->grade;

            // 随机打乱年级内的运动员
            $shuffledAthletes = $gradeAthletes->shuffle()->values(); // 使用values()重置键

            // 计算需要多少个分组
            $heatCount = ceil($shuffledAthletes->count() / $maxLanes);

            for ($i = 0; $i < $heatCount; $i++) {
                $heatAthletes = $shuffledAthletes->slice($i * $maxLanes, $maxLanes)->values(); // 重置键

                if ($heatAthletes->isEmpty()) {
                    continue;
                }

                $heat = $competitionEvent->heats()->create([
                    'grade_id' => $grade->id,
                    'heat_number' => $i + 1,
                    'total_lanes' => $maxLanes
                ]);

                // 从1号赛道开始连续分配
                foreach ($heatAthletes as $index => $athlete) {
                    $lane = $heat->lanes()->create([
                        'lane_number' => $index + 1
                    ]);

                    $lane->laneAthletes()->create([
                        'athlete_id' => $athlete->id
                    ]);
                }

                $generatedCount++;
            }
        }

        return $generatedCount;
    }

    private function generateLongDistanceHeats($competitionEvent, $athletes)
    {
        $generatedCount = 0;

        // 按年级分组（类似田赛）
        $athletesByGrade = $athletes->groupBy(function($athlete) {
            return $athlete->klass->grade_id;
        });

        foreach ($athletesByGrade as $gradeId => $gradeAthletes) {
            $grade = $gradeAthletes->first()->klass->grade;

            // 随机打乱年级内的运动员顺序
            $shuffledAthletes = $gradeAthletes->shuffle()->values(); // 使用values()重置键

            // 为该年级创建一个分组
            $heat = $competitionEvent->heats()->create([
                'grade_id' => $grade->id,
                'heat_number' => 1,
                'total_lanes' => $shuffledAthletes->count()  // 人数即为总位置数
            ]);

            // 为每个运动员分配位置
            foreach ($shuffledAthletes as $index => $athlete) {
                $lane = $heat->lanes()->create([
                    'lane_number' => $index + 1,
                    'position' => $index + 1
                ]);

                $lane->laneAthletes()->create([
                    'athlete_id' => $athlete->id
                ]);
            }

            $generatedCount++;
        }

        return $generatedCount;
    }

    public function generateFieldEvents(Competition $competition)
    {
        // 为所有田赛项目生成分组
        $fieldEvents = $competition->competitionEvents()
            ->whereHas('event', function($query) {
                $query->where('event_type', 'field');
            })
            ->with([
                'event',
                'athleteCompetitionEvents.athlete.klass.grade'
            ])
            ->get();

        $generatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($fieldEvents as $competitionEvent) {
                // 清除旧的分组
                $competitionEvent->heats()->delete();

                // 获取所有报名的运动员
                $athletes = $competitionEvent->athleteCompetitionEvents->map->athlete;

                if ($athletes->isEmpty()) {
                    continue;
                }

                // 田赛项目：按年级分组，不限人数
                $athletesByGrade = $athletes->groupBy(function($athlete) {
                    return $athlete->klass->grade_id;
                });

                foreach ($athletesByGrade as $gradeId => $gradeAthletes) {
                    $grade = $gradeAthletes->first()->klass->grade;

                    // 随机打乱年级内的运动员顺序
                    $shuffledAthletes = $gradeAthletes->shuffle()->values(); // 使用values()重置键

                    // 为该年级创建一个分组
                    $heat = $competitionEvent->heats()->create([
                        'grade_id' => $grade->id,
                        'heat_number' => 1,  // 田赛每个年级只有一组
                        'total_lanes' => $shuffledAthletes->count()  // 人数即为总位置数
                    ]);

                    // 为每个运动员分配位置
                    foreach ($shuffledAthletes as $index => $athlete) {
                        $lane = $heat->lanes()->create([
                            'lane_number' => $index + 1,
                            'position' => $index + 1  // position表示试跳/试投顺序
                        ]);

                        $lane->laneAthletes()->create([
                            'athlete_id' => $athlete->id
                        ]);
                    }

                    $generatedCount++;
                }
            }

            DB::commit();

            return redirect()->route('competitions.heats.index', $competition)
                ->with('success', "成功生成 {$generatedCount} 个田赛分组");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Generate field heats failed', ['error' => $e->getMessage()]);

            return redirect()->route('competitions.heats.index', $competition)
                ->with('error', '生成田赛分组失败: ' . $e->getMessage());
        }
    }

    public function show(Competition $competition, Heat $heat)
    {
        $heat->load([
            'grade',
            'competitionEvent.event',
            'lanes' => function($query) {
                $query->with(['laneAthletes.athlete.klass.grade'])->orderBy('lane_number');
            }
        ]);

        $competitionEvent = $heat->competitionEvent;

        return view('heats.show', compact('competition', 'heat', 'competitionEvent'));
    }

    public function edit(Competition $competition, Heat $heat)
    {
        $heat->load([
            'grade',
            'competitionEvent.event',
            'lanes.laneAthletes.athlete.klass'
        ]);

        $competitionEvent = $heat->competitionEvent;

        // 获取同项目、同年级的其他分组中的运动员
        $availableAthletes = Athlete::whereHas('laneAthletes.lane.heat', function($query) use ($competitionEvent, $heat) {
                $query->where('competition_event_id', $competitionEvent->id)
                      ->where('grade_id', $heat->grade_id)
                      ->where('id', '!=', $heat->id);
            })
            ->with('klass.grade')
            ->get();

        // 获取已报名但未分组的运动员（同年级）
        $registeredAthleteIds = $competitionEvent->athleteCompetitionEvents()
            ->whereHas('athlete.klass', function($query) use ($heat) {
                $query->where('grade_id', $heat->grade_id);
            })
            ->pluck('athlete_id')
            ->toArray();

        $groupedAthleteIds = Athlete::whereHas('laneAthletes.lane.heat', function($query) use ($competitionEvent, $heat) {
                $query->where('competition_event_id', $competitionEvent->id)
                      ->where('grade_id', $heat->grade_id);
            })
            ->pluck('id')
            ->toArray();

        $ungroupedAthleteIds = array_diff($registeredAthleteIds, $groupedAthleteIds);

        $ungroupedAthletes = Athlete::whereIn('id', $ungroupedAthleteIds)
            ->with('klass.grade')
            ->get();

        return view('heats.edit', compact('competition', 'heat', 'competitionEvent', 'availableAthletes', 'ungroupedAthletes'));
    }

    public function update(Request $request, Competition $competition, Heat $heat)
    {
        // 处理运动员操作
        if ($request->action_type === 'add_athlete') {
            return $this->addAthleteToHeat($request, $competition, $heat);
        } elseif ($request->action_type === 'remove_athlete') {
            return $this->removeAthleteFromHeat($request, $competition, $heat);
        }

        // 普通更新
        $validated = $request->validate([
            'heat_number' => 'nullable|integer|min:1',
            'total_lanes' => 'nullable|integer|min:1'
        ]);

        $heat->update($validated);

        return redirect()->route('competitions.heats.show', [$competition, $heat])
            ->with('success', '分组信息更新成功');
    }

    public function destroy(Competition $competition, Heat $heat)
    {
        $heat->delete();

        return redirect()->route('competitions.heats.index', $competition)
            ->with('success', '分组已删除');
    }

    private function addAthleteToHeat(Request $request, Competition $competition, Heat $heat)
    {
        $validated = $request->validate([
            'athlete_id' => 'required|exists:athletes,id',
            'lane_number' => 'required|integer|min:1',
            'relay_position' => 'nullable|integer|min:1|max:4'
        ]);

        $athlete = Athlete::findOrFail($validated['athlete_id']);
        $competitionEvent = $heat->competitionEvent;

        $isRelay = str_contains($competitionEvent->event->name, '接力');

        // 检查年级是否匹配
        if ($heat->grade_id && $athlete->klass->grade_id != $heat->grade_id) {
            return redirect()->route('competitions.heats.edit', [$competition, $heat])
                ->with('error', '该运动员年级与当前分组不符');
        }

        // 检查该运动员是否已经在当前分组中
        $existsInHeat = $heat->lanes()->whereHas('laneAthletes', function($query) use ($athlete) {
            $query->where('athlete_id', $athlete->id);
        })->exists();

        if ($existsInHeat) {
            return redirect()->route('competitions.heats.edit', [$competition, $heat])
                ->with('error', '该运动员已经在当前分组中');
        }

        // 接力项目需要棒次
        if ($isRelay && empty($validated['relay_position'])) {
            return redirect()->route('competitions.heats.edit', [$competition, $heat])
                ->with('error', '接力项目需要指定棒次');
        }

        DB::beginTransaction();
        try {
            // 从原分组中移除该运动员
            LaneAthlete::whereHas('lane.heat', function($query) use ($competitionEvent) {
                    $query->where('competition_event_id', $competitionEvent->id);
                })
                ->where('athlete_id', $athlete->id)
                ->delete();

            // 添加到新分组
            $lane = $heat->lanes()->firstOrCreate([
                'lane_number' => $validated['lane_number']
            ]);

            $lane->laneAthletes()->create([
                'athlete_id' => $athlete->id,
                'relay_position' => $validated['relay_position'] ?? null
            ]);

            DB::commit();

            $message = $isRelay
                ? "运动员已添加到第 {$validated['lane_number']} 赛道第 {$validated['relay_position']} 棒"
                : "运动员已添加到第 {$validated['lane_number']} 赛道";

            return redirect()->route('competitions.heats.edit', [$competition, $heat])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('competitions.heats.edit', [$competition, $heat])
                ->with('error', '添加失败：' . $e->getMessage());
        }
    }

    private function removeAthleteFromHeat(Request $request, Competition $competition, Heat $heat)
    {
        $validated = $request->validate([
            'athlete_id' => 'required|exists:athletes,id'
        ]);

        $laneAthlete = LaneAthlete::whereHas('lane', function($query) use ($heat) {
                $query->where('heat_id', $heat->id);
            })
            ->where('athlete_id', $validated['athlete_id'])
            ->first();

        if (!$laneAthlete) {
            return redirect()->route('competitions.heats.edit', [$competition, $heat])
                ->with('error', '该运动员不在当前分组中');
        }

        DB::beginTransaction();
        try {
            $lane = $laneAthlete->lane;
            $laneAthlete->delete();

            // 如果赛道没有其他运动员了，删除该赛道
            if ($lane->laneAthletes()->count() === 0) {
                $lane->delete();
            }

            DB::commit();

            return redirect()->route('competitions.heats.edit', [$competition, $heat])
                ->with('success', '运动员已从分组中移除');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('competitions.heats.edit', [$competition, $heat])
                ->with('error', '移除失败：' . $e->getMessage());
        }
    }
}
