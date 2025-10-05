@extends('layouts.app')

@section('title', '年级管理')

@section('content')
<div class="container mx-auto max-w-4xl">
    <!-- 步骤条 -->
    <div class="mb-6">
        <ul class="steps steps-horizontal w-full">
            <li class="step step-primary">创建运动会</li>
            <li class="step step-primary">设置年级</li>
            <li class="step">登记运动员</li>
            <li class="step">生成分组</li>
            <li class="step">安排日程</li>
        </ul>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex justify-between items-center mb-4">
                <h2 class="card-title text-2xl">参赛年级管理</h2>
                <a href="{{ route('competitions.grades.create', $competition) }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    添加年级
                </a>
            </div>

            @if($grades->isEmpty())
                <div class="alert alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>还没有添加参赛年级，请点击"添加年级"开始</span>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th class="w-16">#</th>
                                <th>年级名称</th>
                                <th>班级数</th>
                                <th>运动员数</th>
                                <th class="w-48">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grades as $index => $grade)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-semibold">{{ $grade->name }}</td>
                                    <td>
                                        @if($grade->klasses->count() > 0)
                                            <span class="badge badge-primary">{{ $grade->klasses->count() }}个班</span>
                                        @else
                                            <span class="text-gray-400">未设置</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $athleteCount = $grade->klasses->sum(fn($k) => $k->athletes->count());
                                        @endphp
                                        @if($athleteCount > 0)
                                            <span class="badge badge-success">{{ $athleteCount }}人</span>
                                        @else
                                            <span class="text-gray-400">未登记</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('competitions.grades.edit', [$competition, $grade]) }}" 
                                               class="btn btn-sm btn-ghost">
                                                编辑
                                            </a>
                                            <form action="{{ route('competitions.grades.destroy', [$competition, $grade]) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('确定要删除该年级吗？删除后该年级下的所有班级和运动员也会被删除。');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-error">删除</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-actions justify-between mt-6">
                    <a href="{{ route('competitions.show', $competition) }}" class="btn btn-ghost">
                        返回运动会
                    </a>
                    <a href="{{ route('competitions.athletes.index', $competition) }}" class="btn btn-primary">
                        下一步：登记运动员
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- 提示信息 -->
    <div class="alert alert-warning mt-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
            <h3 class="font-bold">使用提示</h3>
            <div class="text-sm">
                <p>1. 年级名称建议使用：一年级、二年级、初一、初二、高一、高二等</p>
                <p>2. 年级的显示顺序按照添加顺序自动排列</p>
                <p>3. 添加年级后，下一步可以为每个年级登记运动员</p>
            </div>
        </div>
    </div>
</div>
@endsection
