@extends('layouts.app')

@section('title', '运动员管理')

@section('content')
<div class="container mx-auto max-w-6xl">
    <!-- 步骤条 -->
    <div class="mb-6">
        <ul class="steps steps-horizontal w-full">
            <li class="step step-primary">创建运动会</li>
            <li class="step step-primary">设置年级</li>
            <li class="step step-primary">登记运动员</li>
            <li class="step">生成分组</li>
            <li class="step">安排日程</li>
        </ul>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex justify-between items-center mb-4">
                <h2 class="card-title text-2xl">运动员列表</h2>
                <div class="space-x-2">
                    <form action="{{ route('competitions.athletes.remove-all', $competition) }}" method="POST" class="inline"
                          onsubmit="return confirm('确认要清空全部运动员吗?');">
                        @csrf
                        <button type="submit" class="btn btn-error">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            清空全部
                        </button>
                    </form>
                    <button onclick="import_modal.showModal()" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        批量导入
                    </button>
                </div>
            </div>

            @if($grades->isEmpty())
                <div class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>请先<a href="{{ route('competitions.grades.index', $competition) }}" class="link">添加年级信息</a>后再进行运动员登记</span>
                </div>
            @else
                <!-- 年级选项卡 -->
                <div role="tablist" class="tabs tabs-lifted">
                    @foreach($grades as $index => $grade)
                        <input type="radio" name="grade_tabs" role="tab" class="tab"
                               aria-label="{{ $grade->name }}" id="tab-{{ $grade->id }}"
                               {{ $index === 0 ? 'checked="checked"' : '' }} />
                        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                            <div class="flex justify-end mb-4">
                                <a href="{{ route('competitions.athletes.create', ['competition' => $competition, 'grade_id' => $grade->id]) }}"
                                   class="btn btn-primary btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    添加运动员
                                </a>
                            </div>

                            @php
                                $gradeAthletes = $grade->klasses->flatMap(function($klass) {
                                    return $klass->athletes;
                                });
                            @endphp

                            @include('athletes.table', ['athletes' => $gradeAthletes, 'competition' => $competition])
                        </div>
                    @endforeach
                </div>

                <div class="card-actions justify-between mt-6">
                    <a href="{{ route('competitions.grades.index', $competition) }}" class="btn btn-ghost">
                        上一步：年级管理
                    </a>
                    <a href="{{ route('competitions.heats.index', $competition) }}" class="btn btn-primary">
                        下一步：生成分组
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 批量导入模态框 -->
<dialog id="import_modal" class="modal">
    <div class="modal-box max-w-2xl">
        <h3 class="font-bold text-lg mb-4">批量导入运动员</h3>

        <!-- 下载模板 -->
        <div class="mb-4 p-4 bg-base-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium mb-1">📥 下载导入模板</p>
                    <p class="text-sm text-gray-600">使用模板可以确保数据格式正确</p>
                </div>
                <a href="{{ route('competitions.athletes.download-template', $competition) }}"
                   class="btn btn-sm btn-outline btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    下载模板
                </a>
            </div>
        </div>

        <form action="{{ route('competitions.athletes.import', $competition) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-control w-full mb-4">
                <label class="label">
                    <span class="label-text">选择Excel文件</span>
                </label>
                <input type="file" name="file" accept=".xls,.xlsx,.csv" class="file-input file-input-bordered w-full" required />
                <label class="label">
                    <span class="label-text-alt text-gray-500">支持 .xls、.xlsx 和 .csv 格式</span>
                </label>
            </div>

            <div class="alert alert-info mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm">
                    <p class="font-semibold mb-1">Excel格式要求：</p>
                    <ul class="list-disc ml-4 space-y-1">
                        <li>第一行为标题行：年级、班级、姓名、性别、报名项目</li>
                        <li>年级：如"一年级"（必须已在系统中创建）</li>
                        <li>班级：如"1班"（系统会自动创建）</li>
                        <li>性别：男 或 女</li>
                        <li>报名项目：多个项目用逗号分隔，如"100米,跳远"</li>
                    </ul>
                </div>
            </div>

            <div class="modal-action">
                <button type="submit" class="btn btn-primary">开始导入</button>
                <button type="button" class="btn" onclick="import_modal.close()">取消</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>关闭</button>
    </form>
</dialog>
@endsection
