@extends('layouts.app')

@section('title', '编辑分组')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 面包屑导航 -->
    <div class="mb-6">
        <div class="text-sm breadcrumbs">
            <ul>
                <li><a href="{{ route('competitions.heats.index', $competition) }}">返回分组列表</a></li>
                <li class="text-gray-600">
                    {{ $competitionEvent->event->name }} -
                    {{ $heat->grade->name }} -
                    第{{ $heat->heat_number }}组
                </li>
            </ul>
        </div>
    </div>

    @php
        $isRelay = str_contains($competitionEvent->event->name, '接力') || str_contains($competitionEvent->event->name, '4*100');
        $isFieldEvent = $competitionEvent->event->event_type === 'field';
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 左侧：当前分组运动员 -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">当前分组运动员</h2>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>{{ $isFieldEvent ? '位置' : '赛道' }}</th>
                                @if($isRelay)<th>棒次</th>@endif
                                <th>姓名</th>
                                <th>班级</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($heat->lanes->sortBy('lane_number') as $lane)
                                @foreach($lane->laneAthletes->sortBy('relay_position') as $laneAthlete)
                                    <tr>
                                        <td>{{ $lane->lane_number }}</td>
                                        @if($isRelay)
                                            <td>第{{ $laneAthlete->relay_position }}棒</td>
                                        @endif
                                        <td>{{ $laneAthlete->athlete->name }}</td>
                                        <td>{{ $laneAthlete->athlete->klass->name }}</td>
                                        <td>
                                            <form action="{{ route('competitions.heats.update', [$competition, $heat]) }}"
                                                  method="POST"
                                                  class="inline"
                                                  onsubmit="return confirm('确定要移除该运动员吗？');">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="action_type" value="remove_athlete">
                                                <input type="hidden" name="athlete_id" value="{{ $laneAthlete->athlete->id }}">
                                                <button type="submit" class="btn btn-xs btn-error">移除</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="{{ $isRelay ? '5' : '4' }}" class="text-center text-gray-500">
                                        当前分组无运动员
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(!$isFieldEvent)
                    <div class="divider">{{ $isFieldEvent ? '位置布局预览' : '赛道布局预览' }}</div>

                    <!-- 赛道可视化 -->
                    <div class="grid grid-cols-6 gap-2">
                        @for($i = 1; $i <= $heat->total_lanes; $i++)
                            @php
                                $lane = $heat->lanes->firstWhere('lane_number', $i);
                                $hasAthletes = $lane && $lane->laneAthletes->isNotEmpty();
                            @endphp
                            <div class="p-3 rounded border text-center {{ $hasAthletes ? 'bg-primary text-primary-content' : 'bg-base-200 text-gray-600' }}">
                                <div class="text-xs font-bold">{{ $isFieldEvent ? '位置' : '赛道' }} {{ $i }}</div>
                                @if($hasAthletes)
                                    @if($isRelay)
                                        {{-- 接力项目：显示人数 --}}
                                        <div class="text-sm mt-1">
                                            {{ $lane->laneAthletes->count() }}/4人
                                        </div>
                                    @else
                                        {{-- 非接力：显示运动员姓名 --}}
                                        <div class="text-sm mt-1 truncate" title="{{ $lane->laneAthletes->first()->athlete->name }}">
                                            {{ $lane->laneAthletes->first()->athlete->name }}
                                        </div>
                                    @endif
                                @else
                                    <div class="text-xs mt-1">空</div>
                                @endif
                            </div>
                        @endfor
                    </div>
                @endif
            </div>
        </div>

        <!-- 右侧：添加运动员 -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">从其他分组添加运动员</h2>
                <p class="text-sm text-gray-600 mb-2">
                    限制：同年级 ({{ $heat->grade->name }})、同项目、同性别
                </p>

                @if($availableAthletes->isNotEmpty() || $ungroupedAthletes->isNotEmpty())
                    <form action="{{ route('competitions.heats.update', [$competition, $heat]) }}"
                          method="POST"
                          id="addAthleteForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="action_type" value="add_athlete">

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">选择运动员</span>
                            </label>
                            <select name="athlete_id" class="select select-bordered" required id="athlete_select">
                                <option value="">-- 请选择 --</option>

                                @if($ungroupedAthletes->isNotEmpty())
                                    <optgroup label="⚠️ 已报名但未分组">
                                        @foreach($ungroupedAthletes as $athlete)
                                            <option value="{{ $athlete->id }}">
                                                {{ $athlete->name }} ({{ $athlete->klass->name }}) - 未分组
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                @if($availableAthletes->isNotEmpty())
                                    <optgroup label="📋 其他分组的运动员">
                                        @foreach($availableAthletes as $athlete)
                                            @php
                                                $laneAthlete = $athlete->laneAthletes()
                                                    ->whereHas('lane.heat', function($q) use ($competitionEvent) {
                                                        $q->where('competition_event_id', $competitionEvent->id);
                                                    })
                                                    ->with('lane.heat')
                                                    ->first();
                                                $currentHeat = $laneAthlete?->lane?->heat;
                                                $laneNumber = $laneAthlete?->lane?->lane_number;
                                                $statusText = $currentHeat ? "第{$currentHeat->heat_number}组 - {$laneNumber}" . ($isFieldEvent ? '位置' : '赛道') : '';
                                            @endphp
                                            <option value="{{ $athlete->id }}">
                                                {{ $athlete->name }} ({{ $athlete->klass->name }}) - {{ $statusText }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            <label class="label">
                                <span class="label-text-alt text-gray-500">
                                    显示同年级的其他分组运动员和未分组运动员
                                </span>
                            </label>
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">分配到{{ $isFieldEvent ? '位置' : '赛道' }}</span>
                            </label>
                            <select name="lane_number" class="select select-bordered" required id="lane_select">
                                <option value="">-- 请选择 --</option>
                                @for($i = 1; $i <= $heat->total_lanes; $i++)
                                    @php
                                        $lane = $heat->lanes->firstWhere('lane_number', $i);
                                    @endphp
                                    @if($isRelay)
                                        {{-- 接力项目：显示赛道及其运动员数量 --}}
                                        <option value="{{ $i }}">
                                            第 {{ $i }} 赛道 {{ $lane ? "({$lane->laneAthletes->count()}/4人)" : "(空)" }}
                                        </option>
                                    @else
                                        {{-- 非接力项目：已占用的赛道禁用 --}}
                                        <option value="{{ $i }}" {{ $lane ? 'disabled' : '' }}>
                                            第 {{ $i }} {{ $isFieldEvent ? '位置' : '赛道' }}
                                            {{ $lane ? '(已占用)' : '' }}
                                        </option>
                                    @endif
                                @endfor
                            </select>
                        </div>

                        @if($isRelay)
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text">棒次</span>
                                </label>
                                <select name="relay_position" class="select select-bordered" required id="relay_position_select">
                                    <option value="">-- 请选择 --</option>
                                    @for($i = 1; $i <= 4; $i++)
                                        <option value="{{ $i }}">第 {{ $i }} 棒</option>
                                    @endfor
                                </select>
                                <label class="label">
                                    <span class="label-text-alt text-warning">
                                        ⚠️ 接力项目每个赛道需要4名运动员（4个棒次）
                                    </span>
                                </label>
                            </div>

                            <!-- 显示所选赛道的棒次占用情况 -->
                            <div id="lane_status" class="alert alert-info text-sm mb-4" style="display: none;">
                                <div id="lane_status_content"></div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const laneSelect = document.getElementById('lane_select');
                                    const laneStatus = document.getElementById('lane_status');
                                    const laneStatusContent = document.getElementById('lane_status_content');

                                    // 赛道占用情况数据
                                    const laneData = {
                                        @foreach($heat->lanes as $lane)
                                            {{ $lane->lane_number }}: [
                                                @foreach($lane->laneAthletes->sortBy('relay_position') as $la)
                                                    { position: {{ $la->relay_position }}, name: '{{ $la->athlete->name }}' },
                                                @endforeach
                                            ],
                                        @endforeach
                                    };

                                    laneSelect.addEventListener('change', function() {
                                        const laneNum = parseInt(this.value);
                                        if (laneNum && laneData[laneNum]) {
                                            const athletes = laneData[laneNum];
                                            let html = '<strong>第' + laneNum + '赛道当前状态：</strong><br>';
                                            for (let i = 1; i <= 4; i++) {
                                                const athlete = athletes.find(a => a.position === i);
                                                if (athlete) {
                                                    html += '第' + i + '棒: ' + athlete.name + ' ✓<br>';
                                                } else {
                                                    html += '第' + i + '棒: 空缺 ⚠️<br>';
                                                }
                                            }
                                            laneStatusContent.innerHTML = html;
                                            laneStatus.style.display = 'block';
                                        } else if (laneNum) {
                                            laneStatusContent.innerHTML = '<strong>第' + laneNum + '赛道：</strong>空赛道';
                                            laneStatus.style.display = 'block';
                                        } else {
                                            laneStatus.style.display = 'none';
                                        }
                                    });
                                });
                            </script>
                        @endif

                        <div class="card-actions justify-end">
                            <button type="submit" class="btn btn-primary">添加运动员</button>
                        </div>
                    </form>

                    <div class="divider">运动员状态列表</div>

                    <!-- 可用运动员表格 -->
                    <div class="overflow-x-auto max-h-96">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>姓名</th>
                                    <th>班级</th>
                                    <th>当前状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($ungroupedAthletes->isNotEmpty())
                                    <tr class="bg-warning bg-opacity-20">
                                        <td colspan="3" class="font-bold text-xs">
                                            ⚠️ 已报名但未分组的运动员
                                        </td>
                                    </tr>
                                    @foreach($ungroupedAthletes as $athlete)
                                        <tr class="bg-warning bg-opacity-10">
                                            <td>{{ $athlete->name }}</td>
                                            <td>{{ $athlete->klass->name }}</td>
                                            <td class="text-xs">
                                                <span class="badge badge-sm badge-warning">未分组</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif

                                @if($availableAthletes->isNotEmpty())
                                    <tr class="bg-base-200">
                                        <td colspan="3" class="font-bold text-xs">其他分组的运动员</td>
                                    </tr>
                                    @foreach($availableAthletes as $athlete)
                                        @php
                                            $laneAthlete = $athlete->laneAthletes()
                                                ->whereHas('lane.heat', function($q) use ($competitionEvent) {
                                                    $q->where('competition_event_id', $competitionEvent->id);
                                                })
                                                ->with('lane.heat')
                                                ->first();
                                            $currentHeat = $laneAthlete?->lane?->heat;
                                            $laneNumber = $laneAthlete?->lane?->lane_number;
                                        @endphp
                                        <tr>
                                            <td>{{ $athlete->name }}</td>
                                            <td>{{ $athlete->klass->name }}</td>
                                            <td class="text-xs text-gray-600">
                                                @if($currentHeat && $laneNumber)
                                                    <span class="badge badge-sm badge-info">
                                                        第{{ $currentHeat->heat_number }}组 - {{ $laneNumber }}{{ $isFieldEvent ? '位置' : '赛道' }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-sm">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>没有可添加的运动员</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 flex gap-4">
        <a href="{{ route('competitions.heats.index', $competition) }}" class="btn btn-outline">返回分组列表</a>
        <a href="{{ route('competitions.heats.show', [$competition, $heat]) }}" class="btn btn-ghost">查看详情</a>
    </div>
</div>
@endsection
