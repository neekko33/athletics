@extends('layouts.app')

@section('title', '赛事分组')

@section('content')
<div class="container mx-auto max-w-7xl">
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

    <!-- 径赛分组 -->
    <div class="card bg-base-100 shadow-sm mb-8">
        <div class="card-body">
            <div class="flex justify-between items-center mb-6">
                <h2 class="card-title text-2xl">径赛分组</h2>
                <form action="{{ route('competitions.heats.generate-all', $competition) }}" method="POST"
                      onsubmit="return confirm('自动生成将清除所有现有径赛分组并重新分配（按年级分组），确定继续吗？');">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        自动生成径赛分组
                    </button>
                </form>
            </div>

            @if($trackEvents->isEmpty())
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-gray-500">暂无径赛项目</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($trackEvents as $competitionEvent)
                        <div class="border border-base-300 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-semibold">
                                    {{ $competitionEvent->event->name }}
                                    <span class="badge badge-primary ml-2">{{ $competitionEvent->event->gender }}</span>
                                </h3>
                                <div class="text-sm text-gray-600">
                                    报名人数: {{ $competitionEvent->athleteCompetitionEvents->count() }} 人
                                </div>
                            </div>

                            @if($competitionEvent->heats->isEmpty())
                                <p class="text-gray-500 text-sm">暂无分组，请点击"自动生成径赛分组"</p>
                            @else
                                <div class="space-y-4">
                                    @foreach($competitionEvent->heats->sortBy(fn($h) => [$h->grade->order ?? 999, $h->heat_number]) as $heat)
                                        <div class="border border-gray-200 rounded-lg p-4 bg-base-50">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="font-semibold">
                                                    {{ $heat->grade->name }} - 第 {{ $heat->heat_number }} 组
                                                </h4>
                                                <div class="space-x-2">
                                                    <a href="{{ route('competitions.heats.show', [$competition, $heat]) }}"
                                                       class="btn btn-sm btn-ghost">查看详情</a>
                                                    <a href="{{ route('competitions.heats.edit', [$competition, $heat]) }}"
                                                       class="btn btn-sm btn-ghost">编辑</a>
                                                    <form action="{{ route('competitions.heats.destroy', [$competition, $heat]) }}"
                                                          method="POST" class="inline"
                                                          onsubmit="return confirm('确定删除此分组吗？');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-error btn-ghost">删除</button>
                                                    </form>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                                                @for($i = 1; $i <= $heat->total_lanes; $i++)
                                                    @php
                                                        $lane = $heat->lanes->firstWhere('lane_number', $i);
                                                        $hasAthletes = $lane && $lane->laneAthletes->count() > 0;
                                                    @endphp
                                                    <div class="border rounded p-2 text-center {{ $hasAthletes ? 'bg-blue-50 border-blue-200' : 'bg-gray-50' }}">
                                                        <div class="text-xs text-gray-500 mb-1">第 {{ $i }} 道</div>
                                                        @if($hasAthletes)
                                                            @foreach($lane->laneAthletes as $laneAthlete)
                                                                <div class="text-sm font-medium">{{ $laneAthlete->athlete->name }}</div>
                                                                @if($laneAthlete->relay_position)
                                                                    <div class="text-xs text-gray-500">(棒{{ $laneAthlete->relay_position }})</div>
                                                                @endif
                                                                <div class="text-xs text-gray-500">{{ $laneAthlete->athlete->klass->name }}</div>
                                                            @endforeach
                                                        @else
                                                            <div class="text-xs text-gray-400">空</div>
                                                        @endif
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- 田赛分组 -->
    <div class="card bg-base-100 shadow-sm mb-8">
        <div class="card-body">
            <div class="flex justify-between items-center mb-6">
                <h2 class="card-title text-2xl">田赛分组</h2>
                <form action="{{ route('competitions.heats.generate-field-events', $competition) }}" method="POST"
                      onsubmit="return confirm('自动生成将清除所有现有田赛分组并重新分配（按年级分组），确定继续吗？');">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        自动生成田赛分组
                    </button>
                </form>
            </div>

            @if($fieldEvents->isEmpty())
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-gray-500">暂无田赛项目</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($fieldEvents as $competitionEvent)
                        <div class="border border-base-300 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-semibold">
                                    {{ $competitionEvent->event->name }}
                                    <span class="badge badge-secondary ml-2">{{ $competitionEvent->event->gender }}</span>
                                </h3>
                                <div class="text-sm text-gray-600">
                                    报名人数: {{ $competitionEvent->athleteCompetitionEvents->count() }} 人
                                </div>
                            </div>

                            @if($competitionEvent->heats->isEmpty())
                                <p class="text-gray-500 text-sm">暂无分组，请点击"自动生成田赛分组"</p>
                            @else
                                <div class="space-y-4">
                                    @foreach($competitionEvent->heats->sortBy(fn($h) => $h->grade->order ?? 999) as $heat)
                                        <div class="border border-gray-200 rounded-lg p-4 bg-base-50">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="font-semibold">
                                                    {{ $heat->grade->name }}（共 {{ $heat->lanes->count() }} 人）
                                                </h4>
                                                <div class="space-x-2">
                                                    <a href="{{ route('competitions.heats.show', [$competition, $heat]) }}"
                                                       class="btn btn-sm btn-ghost">查看详情</a>
                                                    <form action="{{ route('competitions.heats.destroy', [$competition, $heat]) }}"
                                                          method="POST" class="inline"
                                                          onsubmit="return confirm('确定删除此分组吗？');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-error btn-ghost">删除</button>
                                                    </form>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                                                @foreach($heat->lanes->sortBy('position') as $lane)
                                                    @php
                                                        $hasAthletes = $lane->laneAthletes->count() > 0;
                                                    @endphp
                                                    <div class="border rounded p-2 text-center {{ $hasAthletes ? 'bg-green-50 border-green-200' : 'bg-gray-50' }}">
                                                        <div class="text-xs text-gray-500 mb-1">第 {{ $lane->position }} 位</div>
                                                        @if($hasAthletes)
                                                            @foreach($lane->laneAthletes as $laneAthlete)
                                                                <div class="text-sm font-medium">{{ $laneAthlete->athlete->name }}</div>
                                                                <div class="text-xs text-gray-500">{{ $laneAthlete->athlete->klass->name }}</div>
                                                            @endforeach
                                                        @else
                                                            <div class="text-xs text-gray-400">空</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- 导航按钮 -->
    <div class="flex justify-between mb-6">
        <a href="{{ route('competitions.athletes.index', $competition) }}" class="btn btn-ghost">
            上一步：运动员管理
        </a>
        <a href="{{ route('competitions.schedules.index', $competition) }}" class="btn btn-primary">
            下一步：日程安排
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    </div>

    <!-- 提示信息 -->
    <div class="alert alert-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <h3 class="font-bold">分组规则说明</h3>
            <div class="text-sm">
                <p><strong>径赛：</strong>按年级分组，每组最多{{ $competition->track_lanes }}人；接力项目按班级分组，每队4人</p>
                <p><strong>长距离（800米/1000米/1500米）：</strong>按年级分组，不限人数（类似田赛）</p>
                <p><strong>田赛：</strong>按年级分组，不限人数，position表示试跳/试投顺序</p>
            </div>
        </div>
    </div>
</div>
@endsection
