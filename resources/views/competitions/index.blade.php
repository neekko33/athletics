@extends('layouts.app')

@section('title', '运动会列表')

@section('content')
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">运动会列表</h2>
        <a href="{{ route('competitions.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            创建运动会
        </a>
    </div>

    @if($competitions->isEmpty())
        <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>还没有创建任何运动会，点击右上角"创建运动会"开始</span>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($competitions as $competition)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title">{{ $competition->name }}</h3>
                        <div class="space-y-2 text-sm">
                            <p>
                                <span class="font-semibold">开始日期:</span>
                                {{ $competition->start_date->format('Y年m月d日') }}
                            </p>
                            <p>
                                <span class="font-semibold">结束日期:</span>
                                {{ $competition->end_date->format('Y年m月d日') }}
                            </p>
                            <p>
                                <span class="font-semibold">赛道数:</span>
                                {{ $competition->track_lanes }}条
                            </p>
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <a href="{{ route('competitions.show', $competition) }}" class="btn btn-primary btn-sm">查看详情</a>
                            <a href="{{ route('competitions.edit', $competition) }}" class="btn btn-ghost btn-sm">编辑</a>
                            <form action="{{ route('competitions.destroy', $competition) }}" method="POST" class="inline" onsubmit="return confirm('确定要删除这个运动会吗？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-error btn-sm">删除</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
