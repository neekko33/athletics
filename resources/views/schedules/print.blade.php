@php
use App\Helpers\ChineseHelper;
@endphp

@extends('layouts.print')

@section('content')
<h1>竞 赛 日 程</h1>

@foreach($schedulesByDate as $date => $daySchedules)
<div class="date-section">
    <div class="date-header">
        {{ \Carbon\Carbon::parse($date)->format('n月j日') }}上午08:30——12：00
    </div>
    
    @php
        // 按项目类型分组：径赛和田赛
        $trackSchedules = $daySchedules->filter(fn($s) => $s->heat->competitionEvent->event->event_type === 'track')->sortBy('scheduled_at');
        $fieldSchedules = $daySchedules->filter(fn($s) => $s->heat->competitionEvent->event->event_type === 'field')->sortBy('scheduled_at');
    @endphp
    
    @if($trackSchedules->count() > 0)
    <div class="event-type-section">
        <div class="event-type-title">径        赛</div>
        @foreach($trackSchedules as $index => $schedule)
        @php
            $event = $schedule->heat->competitionEvent->event;
            $heat = $schedule->heat;
            $participantCount = $heat->lanes->count();
            $takeCount = ceil($participantCount * 0.6);
            // 构建项目名称
            if ($heat->grade) {
                $genderText = $event->gender === '男' ? '男子组' : ($event->gender === '女' ? '女子组' : $event->gender . '组');
                $eventName = "{$heat->grade->name}{$genderText}{$event->name}";
            } else {
                $eventName = "{$event->name}({$event->gender})";
            }
        @endphp
        <div class="schedule-item">
            {{ $index + 1 }}、{{ $eventName }}预决赛     {{ $participantCount }}人{{ $heat->heat_number }}组  取 {{ $takeCount }}名   {{ $schedule->scheduled_at->format('G:i') }}
        </div>
        @endforeach
    </div>
    @endif
    
    @if($fieldSchedules->count() > 0)
    <div class="event-type-section">
        <div class="event-type-title">田         赛</div>
        @foreach($fieldSchedules as $index => $schedule)
        @php
            $event = $schedule->heat->competitionEvent->event;
            $heat = $schedule->heat;
            $participantCount = $heat->lanes->count();
            $takeCount = ceil($participantCount * 0.6);
            $genderText = $event->gender === '男' ? '男子组' : ($event->gender === '女' ? '女子组' : $event->gender . '组');
            $eventName = "{$heat->grade->name}{$genderText}{$event->name}";
        @endphp
        <div class="schedule-item">
            {{ $index + 1 }}、{{ $eventName }}预决赛       {{ $participantCount }}人      取{{ $takeCount }}名   {{ $schedule->scheduled_at->format('G:i') }}
        </div>
        @endforeach
    </div>
    @endif
</div>
@endforeach

<!-- 班级名单部分 -->
<div class="page-break"></div>
<h1>各 班 级 名 单</h1>

@foreach($grades as $grade)
<div class="grade-section">
    <h2>{{ $grade->name }}组</h2>
    
    @foreach($grade->klasses->sortBy('name') as $klass)
        @php
            $athletes = $klass->athletes->sortBy('number');
        @endphp
        @if($athletes->count() > 0)
        <div class="class-section">
            <h3>{{ ChineseHelper::classNameToChinese($klass->name) }}</h3>
            <div class="class-label">运动员：</div>
            <div class="athletes-list">
                @foreach($athletes as $athlete)
                    <span class="athlete-item">{{ $athlete->number }} {{ $athlete->name }}</span>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach
</div>
@endforeach

<!-- 竞赛分组部分 -->
<div class="page-break"></div>
<h1>竞 赛 分 组</h1>

@php
    // 按年级和性别组织所有heats
    $heatsByGradeGender = $schedules->pluck('heat')->unique('id')->groupBy(function ($heat) {
        $event = $heat->competitionEvent->event;
        return ($heat->grade->name ?? '其他') . '|' . $event->gender . '|' . $event->event_type;
    })->sortKeys();
@endphp

@foreach($heatsByGradeGender as $key => $gradeHeats)
@php
    [$gradeName, $gender, $eventType] = explode('|', $key);
    $genderText = $gender === '男' ? '男子组' : '女子组';
    $eventTypeText = $eventType === 'track' ? '径赛' : '田赛';
@endphp
<div class="competition-group-section">
    <h2>{{ $gradeName }} {{ $genderText }} {{ $eventTypeText }}</h2>
    
    @php
        // 按项目分组heats
        $heatsByEvent = $gradeHeats->groupBy(fn($h) => $h->competitionEvent->event->id);
    @endphp
    
    @foreach($heatsByEvent as $eventId => $eventHeats)
    @php
        $event = $eventHeats->first()->competitionEvent->event;
        $eventHeatsSorted = $eventHeats->sortBy('heat_number');
        $totalParticipants = $eventHeatsSorted->sum(fn($h) => $h->lanes->count());
        $totalGroups = $eventHeatsSorted->count();
        $takeCount = ceil($totalParticipants * 0.6);
    @endphp
    
    <div class="event-detail">
        <div class="event-detail-title">
            {{ $loop->iteration }}、{{ $event->name }}预决赛，{{ $totalParticipants }}人{{ $totalGroups }}组，取{{ $takeCount }}名
        </div>
        
        @foreach($eventHeatsSorted as $heat)
        <div class="heat-detail">
            <div class="heat-title">第{{ ChineseHelper::numberToChinese($heat->heat_number) }}组</div>
            
            <table class="heat-table">
                <thead>
                    <tr>
                        <th>道次</th>
                        @foreach($heat->lanes->sortBy('lane_number') as $lane)
                            <th>{{ ['一', '二', '三', '四', '五', '六', '七', '八'][$lane->lane_number - 1] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label-cell">姓名</td>
                        @foreach($heat->lanes->sortBy('lane_number') as $lane)
                            @php
                                $laneAthlete = $lane->laneAthletes->first();
                                $athlete = $laneAthlete ? $laneAthlete->athlete : null;
                            @endphp
                            <td>{{ $athlete ? $athlete->name : '' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="label-cell">号码</td>
                        @foreach($heat->lanes->sortBy('lane_number') as $lane)
                            @php
                                $laneAthlete = $lane->laneAthletes->first();
                                $athlete = $laneAthlete ? $laneAthlete->athlete : null;
                            @endphp
                            <td>{{ $athlete ? $athlete->number : '' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="label-cell">班级</td>
                        @foreach($heat->lanes->sortBy('lane_number') as $lane)
                            @php
                                $laneAthlete = $lane->laneAthletes->first();
                                $athlete = $laneAthlete ? $laneAthlete->athlete : null;
                            @endphp
                            <td>{{ $athlete && $athlete->klass ? ChineseHelper::classNameToChinese($athlete->klass->name) : '' }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endforeach
@endsection
