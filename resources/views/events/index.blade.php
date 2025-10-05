@extends('layouts.app')

@section('title', '运动项目')

@section('content')
    <div class="container mx-auto">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold">运动项目管理</h2>
            <div class="flex gap-2">
                <a href="{{ route('events.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    添加项目
                </a>
                <a href="{{ route('competitions.index') }}" class="btn btn-ghost">返回运动会列表</a>
            </div>
        </div>
        <!-- 统计信息 -->
        <div class="stats shadow w-full mb-6">
            <div class="stat">
                <div class="stat-title">径赛项目</div>
                <div class="stat-value text-primary">{{ $trackEvents->count() }}</div>
                <div class="stat-desc">男子 {{ $trackEvents->where('gender', '男')->count() }} 项 + 女子
                    {{ $trackEvents->where('gender', '女')->count() }} 项</div>
            </div>

            <div class="stat">
                <div class="stat-title">田赛项目</div>
                <div class="stat-value text-secondary">{{ $fieldEvents->count() }}</div>
                <div class="stat-desc">男子 {{ $fieldEvents->where('gender', '男')->count() }} 项 + 女子
                    {{ $fieldEvents->where('gender', '女')->count() }} 项</div>
            </div>

            <div class="stat">
                <div class="stat-title">总计</div>
                <div class="stat-value text-accent">{{ $trackEvents->count() + $fieldEvents->count() }}</div>
                <div class="stat-desc">全部运动项目</div>
            </div>
        </div>
        <!-- 径赛项目 -->
        <div class="mb-8">
            <h3 class="text-2xl font-bold mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                径赛项目
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- 男子径赛 -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title text-primary">男子项目</h4>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>项目名称</th>
                                        <th>平均用时</th>
                                        <th>最大参赛人数</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($trackEvents->where('gender', '男') as $event)
                                        <tr>
                                            <td>{{ $event->name }}</td>
                                            <td>{{ $event->avg_time }}分钟</td>
                                            <td>{{ $event->max_participants ?? '不限' }}</td>
                                            <td>
                                                <div class="flex gap-1">
                                                    <a href="{{ route('events.edit', $event) }}"
                                                        class="btn btn-xs btn-ghost">编辑</a>
                                                    <form action="{{ route('events.destroy', $event) }}" method="POST"
                                                        onsubmit="return confirm('确定删除此项目吗？')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-error">删除</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-gray-500">暂无项目</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 女子径赛 -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title text-secondary">女子项目</h4>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>项目名称</th>
                                        <th>平均用时</th>
                                        <th>最大参赛人数</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($trackEvents->where('gender', '女') as $event)
                                        <tr>
                                            <td>{{ $event->name }}</td>
                                            <td>{{ $event->avg_time }}分钟</td>
                                            <td>{{ $event->max_participants ?? '不限' }}</td>
                                            <td>
                                                <div class="flex gap-1">
                                                    <a href="{{ route('events.edit', $event) }}"
                                                        class="btn btn-xs btn-ghost">编辑</a>
                                                    <form action="{{ route('events.destroy', $event) }}" method="POST"
                                                        onsubmit="return confirm('确定删除此项目吗？')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-error">删除</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-gray-500">暂无项目</td>
                                        </tr>
                                    @endforelse
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
                </svg>
                田赛项目
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- 男子田赛 -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title text-accent">男子项目</h4>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>项目名称</th>
                                        <th>平均用时</th>
                                        <th>最大参赛人数</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fieldEvents->where('gender', '男') as $event)
                                        <tr>
                                            <td>{{ $event->name }}</td>
                                            <td>{{ $event->avg_time }}分钟</td>
                                            <td>{{ $event->max_participants ?? '不限' }}</td>
                                            <td>
                                                <div class="flex gap-1">
                                                    <a href="{{ route('events.edit', $event) }}"
                                                        class="btn btn-xs btn-ghost">编辑</a>
                                                    <form action="{{ route('events.destroy', $event) }}" method="POST"
                                                        onsubmit="return confirm('确定删除此项目吗？')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-error">删除</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-gray-500">暂无项目</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 女子田赛 -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title text-success">女子项目</h4>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>项目名称</th>
                                        <th>平均用时</th>
                                        <th>最大参赛人数</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fieldEvents->where('gender', '女') as $event)
                                        <tr>
                                            <td>{{ $event->name }}</td>
                                            <td>{{ $event->avg_time }}分钟</td>
                                            <td>{{ $event->max_participants ?? '不限' }}</td>
                                            <td>
                                                <div class="flex gap-1">
                                                    <a href="{{ route('events.edit', $event) }}"
                                                        class="btn btn-xs btn-ghost">编辑</a>
                                                    <form action="{{ route('events.destroy', $event) }}" method="POST"
                                                        onsubmit="return confirm('确定删除此项目吗？')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-error">删除</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-gray-500">暂无项目</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
