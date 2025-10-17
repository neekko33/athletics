@extends('layouts.app')

@section('title', '日程安排')

@section('content')
    <div class="container mx-auto max-w-7xl">
        <!-- 步骤条 -->
        <div class="mb-6">
            <ul class="steps w-full">
                <li class="step step-primary">创建运动会</li>
                <li class="step step-primary">参赛年级</li>
                <li class="step step-primary">运动员报名</li>
                <li class="step step-primary">径赛分组</li>
                <li class="step step-primary">日程安排</li>
                <li class="step">总览</li>
            </ul>
        </div>

        <div class="bg-white p-6 rounded-md shadow-md">
            <!-- 顶部操作栏 -->
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-bold">日程安排</h3>
            </div>
            @if ($schedules->count() > 0)
                <!-- 日程列表 -->
                <div class="mb-8">
                    @foreach ($schedulesByDate as $date => $daySchedules)
                        <div class="mb-8">
                            <h4 class="text-2xl font-bold mb-4 flex items-center">
                                <span class="badge badge-primary badge-lg mr-3">
                                    {{ \Carbon\Carbon::parse($date)->format('m月d日') }}
                                </span>
                                <span class="text-gray-600 text-base font-normal">
                                    星期{{ ['日', '一', '二', '三', '四', '五', '六'][\Carbon\Carbon::parse($date)->dayOfWeek] }}
                                </span>
                                <span class="ml-auto text-sm font-normal text-gray-500 mr-4">
                                    共 {{ $daySchedules->count() }} 场比赛
                                </span>
                                <a href="{{ route('competitions.schedules.bulk-new', $competition) }}?date={{ $date }}&type=field"
                                    class="btn btn-secondary">
                                    批量添加
                                </a>
                            </h4>
                            <table class="table w-full">
                                <thead>
                                    <tr>
                                        <th>时间</th>
                                        <th>比赛项目</th>
                                        <th>组次</th>
                                        <th>场地</th>
                                        <th>备注</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daySchedules as $schedule)
                                        <tr class="hover">
                                            <td class="whitespace-nowrap">
                                                <div class="flex gap-1">
                                                    <div class="font-medium">{{ $schedule->scheduled_at->format('H:i') }}
                                                    </div>
                                                    @if ($schedule->end_at)
                                                        <div class="text-sm text-gray-500">
                                                            ~ {{ $schedule->end_at->format('H:i') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <p class="font-medium">
                                                        {{ $schedule->heat->competitionEvent->event->gender }}子
                                                        {{ $schedule->heat->competitionEvent->event->name }}</p>
                                                </div>
                                            </td>
                                            <td>{{ $schedule->heat->grade->name }} 第 {{ $schedule->heat->heat_number }} 组
                                            </td>
                                            <td>{{ $schedule->venue ?: '-' }}</td>
                                            <td class="max-w-xs truncate">{{ $schedule->notes ?: '-' }}</td>
                                            <td>
                                                <div class="flex space-x-2">
                                                    {{-- <a href="{{ route('competitions.schedules.edit', [$competition, $schedule]) }}"
                                                        class="btn btn-sm">
                                                        编辑
                                                    </a> --}}
                                                    <form
                                                        action="{{ route('competitions.schedules.destroy', [$competition, $schedule]) }}"
                                                        method="POST" onsubmit="return confirm('确定删除此日程吗？')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-error">
                                                            删除
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 text-lg mb-4">暂无日程安排</p>
                    <p class="text-gray-400 text-sm mb-6">请先生成径赛分组，然后为每个分组安排时间</p>
                    <a href="{{ route('competitions.schedules.bulk-new', $competition) }}?type=field" class="btn btn-secondary">
                        批量添加
                    </a>
                </div>
            @endif

            @if ($heatsWithoutSchedule->count() > 0)
                <!-- 未安排的分组 -->
                <div class="mt-8 border-t pt-6">
                    <h4 class="font-bold text-xl mb-4">未安排的比赛分组</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($heatsWithoutSchedule as $heat)
                            <div class="card bg-gray-50 border border-gray-200">
                                <div class="card-body p-4">
                                    <h5 class="card-title text-base">
                                        {{ $heat->grade->name }} - {{ $heat->competitionEvent->event->name }} - 第
                                        {{ $heat->heat_number }} 组
                                    </h5>
                                    <p class="text-sm text-gray-600">
                                        {{ $heat->competitionEvent->event->gender }} |
                                        {{ $heat->lanes->count() }} 名运动员
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <!-- 底部导航 -->
            <div class="w-full flex justify-end mt-8">
                <a href="{{ route('competitions.schedules.index', $competition) }}" class="btn mr-2">
                    上一步
                </a>
                <a href="{{ route('competitions.schedules.index-field', $competition) }}" class="btn btn-primary">
                    下一步
                </a>
            </div>
        </div>
    </div>
@endsection
