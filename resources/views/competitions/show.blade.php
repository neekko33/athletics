@extends('layouts.app')

@section('title', $competition->name)

@section('content')
    <div class="container mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div class="flex space-x-3 items-end">
                <h2 class="text-3xl font-bold">{{ $competition->name }}</h2>
                <p class="text text-gray-600">
                    {{ $competition->start_date->format('Y年m月d日') }} - {{ $competition->end_date->format('Y年m月d日') }}
                </p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('competitions.edit', $competition) }}" class="btn btn-ghost">编辑</a>
                <a href="{{ route('competitions.index') }}" class="btn btn-ghost">返回列表</a>
            </div>
        </div>

        <!-- 统计数据卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
            <div class="stats shadow bg-white">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="stat-title">运动员总数</div>
                    <div class="stat-value text-primary">{{ $stats['total_athletes'] }}</div>
                </div>
            </div>

            <div class="stats shadow bg-white">
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="stat-title">比赛项目</div>
                    <div class="stat-value text-secondary">{{ $stats['total_events'] }}</div>
                </div>
            </div>

            <div class="stats shadow bg-white">
                <div class="stat">
                    <div class="stat-figure text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="stat-title">分组总数</div>
                    <div class="stat-value text-accent">{{ $stats['total_heats'] }}</div>
                </div>
            </div>

            <div class="stats shadow bg-white">
                <div class="stat">
                    <div class="stat-figure text-success">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="stat-title">已安排日程</div>
                    <div class="stat-value text-success">{{ $stats['scheduled_heats'] }}</div>
                </div>
            </div>
        </div>

        <!-- 功能卡片 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title">年级班级管理</h3>
                    <p>管理参赛年级和班级信息</p>
                    <div class="card-actions justify-end">
                        <a href="{{ route('competitions.grades.index', $competition) }}"
                            class="btn btn-primary btn-sm">管理年级</a>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title">运动员管理</h3>
                    <p>添加、编辑运动员信息，Excel导入</p>
                    <div class="card-actions justify-end">
                        <a href="{{ route('competitions.athletes.index', $competition) }}"
                            class="btn btn-primary btn-sm">管理运动员</a>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title">赛事分组</h3>
                    <p>自动生成径赛、田赛分组</p>
                    <div class="card-actions justify-end">
                        <a href="{{ route('competitions.heats.index', $competition) }}"
                            class="btn btn-primary btn-sm">查看分组</a>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title">日程安排</h3>
                    <p>设置比赛时间和场地</p>
                    <div class="card-actions justify-end">
                        <a href="{{ route('competitions.schedules.index', $competition) }}"
                            class="btn btn-primary btn-sm">管理日程</a>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow-sm border-2 border-success">
                <div class="card-body">
                    <div class="flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <div class="flex-1">
                            <h3 class="card-title text-success">生成秩序册</h3>
                            <p class="text-sm">一键生成 Word 格式秩序册文件，包含竞赛日程、班级名单、竞赛分组等完整信息</p>
                        </div>
                    </div>
                    <div class="card-actions justify-end mt-2">
                        <form action="{{ route('competitions.generate-schedule-book', $competition) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                生成秩序册
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
