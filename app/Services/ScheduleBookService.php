<?php

namespace App\Services;

use App\Models\Competition;
use App\Helpers\ChineseHelper;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;

class ScheduleBookService
{
    /**
     * 生成秩序册 Word 文档
     */
    public function generate(Competition $competition): string
    {
        // 加载模板文件
        $templatePath = public_path('word_template.docx');

        if (!file_exists($templatePath)) {
            throw new \Exception('模板文件不存在：' . $templatePath);
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // 填充基本信息
        $templateProcessor->setValue('competition_name', $competition->name);

        // 填充竞赛日程
        $scheduleContent = $this->generateScheduleContent($competition);
        $templateProcessor->setValue('schedule_content', $scheduleContent);

        // 填充班级名单
        $classRoster = $this->generateClassRoster($competition);
        $templateProcessor->setValue('class_roster', $classRoster);

        // 填充竞赛分组
        $heatGroups = $this->generateHeatGroups($competition);
        $templateProcessor->setValue('heat_groups', $heatGroups);

        // 保存文件
        $fileName = $competition->name . '_秩序册_' . date('YmdHis') . '.docx';
        $savePath = storage_path('app/public/schedules/' . $fileName);

        // 确保目录存在
        if (!file_exists(dirname($savePath))) {
            mkdir(dirname($savePath), 0755, true);
        }

        $templateProcessor->saveAs($savePath);

        return $fileName;
    }

    /**
     * 生成竞赛日程内容
     */
    private function generateScheduleContent(Competition $competition): string
    {
        $schedules = $competition->schedules()
            ->with(['heat.competitionEvent.event', 'heat.grade'])
            ->orderBy('scheduled_at')
            ->get();

        if ($schedules->isEmpty()) {
            return "暂无日程安排\n";
        }

        // 按日期分组
        $schedulesByDate = $schedules->groupBy(function ($schedule) {
            return $schedule->scheduled_at->format('Y-m-d');
        });

        $content = '';
        foreach ($schedulesByDate as $date => $daySchedules) {
            $content .= "\n" . Carbon::parse($date)->isoFormat('M月D日') . "上午08:30——12:00 / 下午14:30——18:00\n";
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
                $content .= "径        赛\n";

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
                    $content .= ($startIndex++) . "、{$eventName}预决赛  {$participantCount}人{$groupCount}组  取{$takeCount}名  " . $firstSchedule->scheduled_at->format('G:i') . "\n";
                }
            }

            // 田赛
            if ($fieldSchedules->count() > 0) {
                $content .= "田         赛\n";
                foreach ($fieldSchedules as $index => $schedule) {
                    $event = $schedule->heat->competitionEvent->event;
                    $heat = $schedule->heat;
                    $participantCount = $heat->lanes->count();
                    $takeCount = ceil($participantCount * 0.6);
                    $genderText = $event->gender === '男' ? '男子组' : ($event->gender === '女' ? '女子组' : $event->gender . '组');
                    $gradeName = $heat->grade ? $heat->grade->name : '';
                    $eventName = $gradeName ? "{$gradeName}{$genderText}{$event->name}" : "{$event->name}({$event->gender})";

                    $content .= ($index + 1) . "、{$eventName}预决赛  {$participantCount}人  取{$takeCount}名  " . $schedule->scheduled_at->format('G:i') . "\n";
                };
            }
            $content .= "\n";
        }

        return $content;
    }

    /**
     * 生成班级名单
     */
    private function generateClassRoster(Competition $competition): string
    {
        $grades = $competition->grades()
            ->with(['klasses.athletes'])
            ->orderBy('order')
            ->get();

        if ($grades->isEmpty()) {
            return "暂无班级名单\n";
        }

        $content = '';
        foreach ($grades as $grade) {
            $content .= "\n" . $grade->name . "组\n";

            foreach ($grade->klasses as $klass) {
                $athletes = $klass->athletes->sortBy('number');
                if ($athletes->count() > 0) {
                    $content .= $klass->name . "\n";

                    // 按性别分组
                    $maleAthletes = $athletes->where('gender', '男');
                    $femaleAthletes = $athletes->where('gender', '女');

                    if ($maleAthletes->count() > 0) {
                        $athleteList = $maleAthletes->map(function ($a) {
                            return $a->number . ' ' . $a->name;
                        })->implode(' ');
                        $content .= $athleteList . "\n";
                    }

                    if ($femaleAthletes->count() > 0) {
                        $athleteList = $femaleAthletes->map(function ($a) {
                            return $a->number . ' ' . $a->name;
                        })->implode(' ');
                        $content .= $athleteList . "\n";
                    }
                }
            }
            $content .= "\n";
        }

        return $content;
    }

    /**
     * 生成竞赛分组
     */
    private function generateHeatGroups(Competition $competition): string
    {
        $schedules = $competition->schedules()
            ->with([
                'heat.competitionEvent.event',
                'heat.grade',
                'heat.lanes.laneAthletes.athlete.klass'
            ])
            ->get();

        if ($schedules->isEmpty()) {
            return "暂无竞赛分组\n";
        }

        // 获取所有年级和班级数据（用于格式化日程）
        $grades = $competition->grades()
            ->with(['klasses.athletes'])
            ->orderBy('order')
            ->get();

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

        $text = '';

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

                $text .= ($startIndex++) . "、{$event->name}预决赛，{$totalParticipants}人" . ($eventType === 'track' ? ($totalGroups . '组') : '') . "，取{$takeCount}名\n";

                foreach ($eventHeatsSorted as $heat) {
                    if ($eventType === 'track') {
                        // 判断是否为中长跑项目，中长跑显示序号不显示道次
                        $middleLongDistanceEvents = ['800米', '1500米', '1000米'];
                        $text .= "第" . ChineseHelper::numberToChinese($heat->heat_number) . "组\n";

                        if (!in_array($event->name, $middleLongDistanceEvents)) {
                            $text .= "道次";
                        } else {
                            $text .= "序号";
                        }

                        foreach ($heat->lanes->sortBy('lane_number') as $lane) {
                            $laneNumbers = ['一', '二', '三', '四', '五', '六', '七', '八'];
                            if (!in_array($event->name, $middleLongDistanceEvents)) {
                                $text .= "\t" . $laneNumbers[$lane->lane_number - 1];
                            } else {
                                $text .= "\t" . $lane->lane_number;
                            }
                        }
                    } else {
                        $text .= "序号";
                        foreach ($heat->lanes->sortBy('lane_number') as $lane) {
                            $text .= "\t" . $lane->lane_number;
                        }
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
