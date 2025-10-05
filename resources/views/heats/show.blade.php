@extends('layouts.app')

@section('title', '分组详情')

@section('content')
<div class="container mx-auto max-w-4xl">
    <!-- 步骤条 -->
    <div class="mb-6">
        <ul class="steps steps-horizontal w-full">
            <li class="step step-primary">创建运动会</li>
            <li class="step step-primary">设置年级</li>
            <li class="step step-primary">登记运动员</li>
            <li class="step step-primary">生成分组</li>
            <li class="step">安排日程</li>
        </ul>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="mb-6">
                <h2 class="card-title text-2xl">
                    {{ $competitionEvent->event->name }} - {{ $heat->grade->name }}
                    @if($heat->heat_number > 1 || $competitionEvent->event->event_type === 'track')
                        第 {{ $heat->heat_number }} 组
                    @endif
                </h2>
                <p class="text-gray-600 mt-2">
                    @if($competitionEvent->event->event_type === 'track')
                        共 {{ $heat->total_lanes }} 条赛道
                    @else
                        共 {{ $heat->lanes->count() }} 人
                    @endif
                </p>
            </div>

            @php
                $isRelay = str_contains($competitionEvent->event->name, '接力');
                $isField = $competitionEvent->event->event_type === 'field';
                $isLongDistance = in_array($competitionEvent->event->name, ['800米', '1000米', '1500米']);
            @endphp

            @if($isField || $isLongDistance)
                <!-- 田赛或长距离项目显示 -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($heat->lanes->sortBy('position') as $lane)
                        @php
                            $hasAthletes = $lane->laneAthletes->count() > 0;
                        @endphp
                        <div class="card {{ $hasAthletes ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }} border">
                            <div class="card-body p-4">
                                <h3 class="text-sm font-semibold text-gray-500 mb-2">第 {{ $lane->position }} 位</h3>
                                @if($hasAthletes)
                                    @foreach($lane->laneAthletes as $laneAthlete)
                                        <div class="bg-white rounded p-2">
                                            <p class="font-medium">{{ $laneAthlete->athlete->name }}</p>
                                            <p class="text-sm text-gray-600">
                                                {{ $laneAthlete->athlete->klass->grade->name }} 
                                                {{ $laneAthlete->athlete->klass->name }}
                                            </p>
                                            @if($laneAthlete->athlete->number)
                                                <p class="text-xs text-gray-500">编号: {{ $laneAthlete->athlete->number }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-gray-400 text-sm text-center py-4">此位置为空</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- 径赛项目显示 -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @for($i = 1; $i <= $heat->total_lanes; $i++)
                        @php
                            $lane = $heat->lanes->firstWhere('lane_number', $i);
                            $hasAthletes = $lane && $lane->laneAthletes->count() > 0;
                        @endphp
                        <div class="card {{ $hasAthletes ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200' }} border">
                            <div class="card-body p-4">
                                <h3 class="text-sm font-semibold text-gray-500 mb-2">第 {{ $i }} 道</h3>
                                @if($hasAthletes)
                                    <div class="space-y-2">
                                        @foreach($lane->laneAthletes->sortBy('relay_position') as $laneAthlete)
                                            <div class="bg-white rounded p-2">
                                                <p class="font-medium">{{ $laneAthlete->athlete->name }}</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $laneAthlete->athlete->klass->grade->name }} 
                                                    {{ $laneAthlete->athlete->klass->name }}
                                                </p>
                                                @if($laneAthlete->relay_position)
                                                    <p class="text-xs text-gray-500">第 {{ $laneAthlete->relay_position }} 棒</p>
                                                @endif
                                                @if($laneAthlete->athlete->number)
                                                    <p class="text-xs text-gray-500">编号: {{ $laneAthlete->athlete->number }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-400 text-sm text-center py-4">此赛道为空</p>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            @endif

            <div class="card-actions justify-between mt-6">
                <a href="{{ route('competitions.heats.index', $competition) }}" class="btn btn-ghost">返回列表</a>
                <a href="{{ route('competitions.heats.edit', [$competition, $heat]) }}" class="btn btn-primary">编辑分组</a>
            </div>
        </div>
    </div>
</div>
@endsection
