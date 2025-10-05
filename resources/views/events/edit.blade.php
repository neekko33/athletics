@extends('layouts.app')

@section('title', '编辑运动项目')

@section('content')
<div class="container mx-auto max-w-2xl">
    <h2 class="text-3xl font-bold mb-6">编辑运动项目</h2>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form action="{{ route('events.update', $event) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- 项目名称 -->
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">项目名称 *</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $event->name) }}"
                           class="input input-bordered w-full @error('name') input-error @enderror"
                           placeholder="例如：100米、跳高、铅球"
                           required
                           autofocus>
                    @error('name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- 项目类型 -->
                <div class="form-control w-full mt-4">
                    <label class="label">
                        <span class="label-text font-semibold">项目类型 *</span>
                    </label>
                    <select name="event_type"
                            class="select select-bordered w-full @error('event_type') select-error @enderror"
                            required>
                        <option value="">请选择</option>
                        <option value="track" {{ old('event_type', $event->event_type) == 'track' ? 'selected' : '' }}>径赛</option>
                        <option value="field" {{ old('event_type', $event->event_type) == 'field' ? 'selected' : '' }}>田赛</option>
                    </select>
                    <label class="label">
                        <span class="label-text-alt">径赛：跑步类项目；田赛：投掷、跳跃类项目</span>
                    </label>
                    @error('event_type')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- 性别 -->
                <div class="form-control w-full mt-4">
                    <label class="label">
                        <span class="label-text font-semibold">性别 *</span>
                    </label>
                    <select name="gender"
                            class="select select-bordered w-full @error('gender') select-error @enderror"
                            required>
                        <option value="">请选择</option>
                        <option value="男" {{ old('gender', $event->gender) == '男' ? 'selected' : '' }}>男</option>
                        <option value="女" {{ old('gender', $event->gender) == '女' ? 'selected' : '' }}>女</option>
                    </select>
                    @error('gender')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- 最大参赛人数 -->
                <div class="form-control w-full mt-4">
                    <label class="label">
                        <span class="label-text font-semibold">最大参赛人数</span>
                    </label>
                    <input type="number"
                           name="max_participants"
                           value="{{ old('max_participants', $event->max_participants) }}"
                           class="input input-bordered w-full @error('max_participants') input-error @enderror"
                           placeholder="留空表示不限制"
                           min="1">
                    <label class="label">
                        <span class="label-text-alt">径赛通常为6人（按跑道数），田赛可不限制</span>
                    </label>
                    @error('max_participants')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- 平均用时 -->
                <div class="form-control w-full mt-4">
                    <label class="label">
                        <span class="label-text font-semibold">平均用时（分钟）</span>
                    </label>
                    <input type="number"
                           name="avg_time"
                           value="{{ old('avg_time', $event->avg_time ?? 5) }}"
                           class="input input-bordered w-full @error('avg_time') input-error @enderror"
                           placeholder="5"
                           min="1">
                    <label class="label">
                        <span class="label-text-alt">用于批量安排日程时，自动计算各项目的时间间隔</span>
                    </label>
                    @error('avg_time')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- 使用情况提示 -->
                @if($event->competitionEvents()->count() > 0)
                    <div class="alert alert-warning mt-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>此项目已被 {{ $event->competitionEvents()->count() }} 个运动会使用，修改后将影响相关运动会</span>
                    </div>
                @endif

                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('events.index') }}" class="btn btn-ghost">取消</a>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
