@extends('layouts.app')

@section('title', '运动项目')

@section('content')
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">运动项目</h2>
        <a href="{{ route('competitions.index') }}" class="btn btn-ghost">返回运动会列表</a>
    </div>

    <!-- 径赛项目 -->
    <div class="mb-8">
        <h3 class="text-2xl font-bold mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            径赛项目
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- 男子径赛 -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h4 class="card-title text-primary">男子项目</h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>项目名称</th>
                                    <th>平均用时</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trackEvents->where('gender', '男') as $event)
                                    <tr>
                                        <td>{{ $event->name }}</td>
                                        <td>{{ $event->avg_time }}分钟</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 女子径赛 -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h4 class="card-title text-secondary">女子项目</h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>项目名称</th>
                                    <th>平均用时</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trackEvents->where('gender', '女') as $event)
                                    <tr>
                                        <td>{{ $event->name }}</td>
                                        <td>{{ $event->avg_time }}分钟</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 田赛项目 -->
    <div class="mb-8">
        <h3 class="text-2xl font-bold mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
            </svg>
            田赛项目
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- 男子田赛 -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h4 class="card-title text-accent">男子项目</h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>项目名称</th>
                                    <th>平均用时</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fieldEvents->where('gender', '男') as $event)
                                    <tr>
                                        <td>{{ $event->name }}</td>
                                        <td>{{ $event->avg_time }}分钟</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 女子田赛 -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h4 class="card-title text-success">女子项目</h4>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>项目名称</th>
                                    <th>平均用时</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fieldEvents->where('gender', '女') as $event)
                                    <tr>
                                        <td>{{ $event->name }}</td>
                                        <td>{{ $event->avg_time }}分钟</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 统计信息 -->
    <div class="stats shadow w-full">
        <div class="stat">
            <div class="stat-title">径赛项目</div>
            <div class="stat-value text-primary">{{ $trackEvents->count() }}</div>
            <div class="stat-desc">男子8项 + 女子8项</div>
        </div>
        
        <div class="stat">
            <div class="stat-title">田赛项目</div>
            <div class="stat-value text-secondary">{{ $fieldEvents->count() }}</div>
            <div class="stat-desc">男子6项 + 女子6项</div>
        </div>
        
        <div class="stat">
            <div class="stat-title">总计</div>
            <div class="stat-value text-accent">{{ $trackEvents->count() + $fieldEvents->count() }}</div>
            <div class="stat-desc">全部运动项目</div>
        </div>
    </div>
</div>
@endsection
