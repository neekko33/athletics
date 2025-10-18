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
                <h3 class="text-2xl font-bold">日程安排 - 田赛</h3>
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
                                <form action="{{ route('competitions.schedules.destroy-all', [$competition]) }}"
                                    method="POST" onsubmit="return confirm('确定删除当天所有日程吗？')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="date" value="{{ $date }}" />
                                    <input type="hidden" name="event_type" value="field" />
                                    <button type="submit" class="btn btn-error btn-dash mr-4">
                                        清空当天日程
                                    </button>
                                </form>
                                <a href="{{ route('competitions.schedules.bulk-new', $competition) }}?date={{ $date }}&type=field"
                                    class="btn btn-secondary">
                                    添加比赛
                                </a>
                            </h4>
                            <table class="table w-full">
                                <thead>
                                    <tr>
                                        <th>时间</th>
                                        <th>比赛项目</th>
                                        <th>性别</th>
                                        <th>分组数量</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daySchedules as $eventName => $schedule)
                                        <tr class="hover">
                                            <td class="whitespace-nowrap">
                                                <div class="flex gap-1">
                                                    <div class="font-medium">{{ $schedule['scheduled_at']->format('H:i') }}
                                                    </div>
                                                    @if ($schedule['end_at'])
                                                        <div class="text-sm text-gray-500">
                                                            ~ {{ $schedule['end_at']->format('H:i') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="font-medium">{{ $schedule['event']->name }}</div>
                                            </td>
                                            <td>
                                                <div class="font-medium">{{ $schedule['event']->gender }}</div>
                                            </td>
                                            <td>
                                                <div class="font-medium">{{ $schedule['schedules']->count() }}</div>
                                            </td>
                                            <td>
                                                <div class="flex space-x-2">
                                                    <form
                                                        action="{{ route('competitions.schedules.destroy', [$competition]) }}"
                                                        method="POST" onsubmit="return confirm('确定删除此日程吗？')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="event_id"
                                                            value="{{ $schedule['event']->id }}" />
                                                        <input type="hidden" name="gender"
                                                            value="{{ $schedule['event']->gender }}" />
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
                    <a href="{{ route('competitions.schedules.bulk-new', $competition) }}?type=field"
                        class="btn btn-secondary">
                        添加比赛
                    </a>
                </div>
            @endif

            @if ($heatsWithoutSchedule->count() > 0)
                <div class="mt-8 border-t pt-6">
                    <h4 class="font-bold text-xl mb-4">未安排的比赛项目</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($heatsWithoutSchedule as $eventName => $heats)
                            <div class="card bg-gray-50 border border-gray-200">
                                <div class="card-body p-4">
                                    <h5 class="card-title text-base">
                                        {{ $eventName }}
                                    </h5>
                                    <p class="text-sm text-gray-600">
                                        {{ $heats->count() }} 个分组
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
                <a href="{{ route('competitions.show', $competition) }}" class="btn btn-primary">
                    完成
                </a>
            </div>
        </div>
    </div>
@endsection
