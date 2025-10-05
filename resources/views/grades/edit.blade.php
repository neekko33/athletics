@extends('layouts.app')

@section('title', '编辑年级')

@section('content')
<div class="container mx-auto max-w-2xl">
    <h2 class="text-3xl font-bold mb-6">编辑年级</h2>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <form action="{{ route('competitions.grades.update', [$competition, $grade]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">年级名称 *</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name', $grade->name) }}" 
                           class="input input-bordered w-full @error('name') input-error @enderror" 
                           placeholder="例如：一年级、初一、高一"
                           required
                           autofocus>
                    @error('name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- 显示相关信息 -->
                <div class="stats shadow mt-4">
                    <div class="stat">
                        <div class="stat-title">班级数</div>
                        <div class="stat-value text-primary text-2xl">{{ $grade->klasses->count() }}</div>
                    </div>
                    
                    <div class="stat">
                        <div class="stat-title">运动员数</div>
                        <div class="stat-value text-secondary text-2xl">
                            {{ $grade->klasses->sum(fn($k) => $k->athletes->count()) }}
                        </div>
                    </div>
                </div>

                @if($grade->klasses->count() > 0)
                    <div class="alert alert-warning mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>该年级下已有 {{ $grade->klasses->count() }} 个班级，修改年级名称不会影响已有数据。</span>
                    </div>
                @endif

                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('competitions.grades.index', $competition) }}" class="btn btn-ghost">取消</a>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 危险操作区 -->
    @if($grade->klasses->isEmpty())
        <div class="card bg-error/10 border-2 border-error mt-6">
            <div class="card-body">
                <h3 class="card-title text-error">危险操作</h3>
                <p>删除此年级将无法恢复。</p>
                <div class="card-actions justify-end">
                    <form action="{{ route('competitions.grades.destroy', [$competition, $grade]) }}" 
                          method="POST"
                          onsubmit="return confirm('确定要删除该年级吗？此操作无法撤销！');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error">删除年级</button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-error mt-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h3 class="font-bold">无法删除</h3>
                <div class="text-sm">该年级下已有班级数据，请先删除所有班级后再删除年级。</div>
            </div>
        </div>
    @endif
</div>
@endsection
