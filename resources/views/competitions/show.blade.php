@extends('layouts.app')

@section('title', $competition->name)

@section('content')
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">{{ $competition->name }}</h2>
        <div class="space-x-2">
            <a href="{{ route('competitions.edit', $competition) }}" class="btn btn-ghost">编辑</a>
            <a href="{{ route('competitions.index') }}" class="btn btn-ghost">返回列表</a>
        </div>
    </div>

    <!-- 统计数据卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="stat-title">运动员总数</div>
                <div class="stat-value text-primary">{{ $stats['total_athletes'] }}</div>
            </div>
        </div>

        <div class="stats shadow">
            <div class="stat">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="stat-title">比赛项目</div>
                <div class="stat-value text-secondary">{{ $stats['total_events'] }}</div>
            </div>
        </div>

        <div class="stats shadow">
            <div class="stat">
                <div class="stat-figure text-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="stat-title">分组总数</div>
                <div class="stat-value text-accent">{{ $stats['total_heats'] }}</div>
            </div>
        </div>

        <div class="stats shadow">
            <div class="stat">
                <div class="stat-figure text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="stat-title">已安排日程</div>
                <div class="stat-value text-success">{{ $stats['scheduled_heats'] }}</div>
            </div>
        </div>
    </div>

    <!-- 功能卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h3 class="card-title">年级班级管理</h3>
                <p>管理参赛年级和班级信息</p>
                <div class="card-actions justify-end">
                    <a href="{{ route('competitions.grades.index', $competition) }}" class="btn btn-primary btn-sm">管理年级</a>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h3 class="card-title">运动员管理</h3>
                <p>添加、编辑运动员信息，Excel导入</p>
                <div class="card-actions justify-end">
                    <a href="{{ route('competitions.athletes.index', $competition) }}" class="btn btn-primary btn-sm">管理运动员</a>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h3 class="card-title">赛事分组</h3>
                <p>自动生成径赛、田赛分组</p>
                <div class="card-actions justify-end">
                    <a href="{{ route('competitions.heats.index', $competition) }}" class="btn btn-primary btn-sm">查看分组</a>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h3 class="card-title">日程安排</h3>
                <p>设置比赛时间和场地</p>
                <div class="card-actions justify-end">
                    <a href="{{ route('competitions.schedules.index', $competition) }}" class="btn btn-primary btn-sm">管理日程</a>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h3 class="card-title">基本信息</h3>
                <div class="space-y-1 text-sm">
                    <p><span class="font-semibold">开始:</span> {{ $competition->start_date->format('Y-m-d') }}</p>
                    <p><span class="font-semibold">结束:</span> {{ $competition->end_date->format('Y-m-d') }}</p>
                    <p><span class="font-semibold">赛道:</span> {{ $competition->track_lanes }}条</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 年级列表 -->
    @if($competition->grades->isNotEmpty())
        <div class="mt-8">
            <h3 class="text-2xl font-bold mb-4">参赛年级</h3>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>年级</th>
                            <th>班级数</th>
                            <th>运动员数</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($competition->grades as $grade)
                            <tr>
                                <td class="font-semibold">{{ $grade->name }}</td>
                                <td>{{ $grade->klasses->count() }}个班</td>
                                <td>{{ $grade->klasses->sum(fn($k) => $k->athletes->count()) }}人</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
