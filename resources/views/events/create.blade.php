@extends('layouts.app')

@section('title', '添加运动项目')

@section('content')
<div class="container mx-auto max-w-2xl">
    <h2 class="text-3xl font-bold mb-6">添加运动项目</h2>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form action="{{ route('events.store') }}" method="POST">
                @csrf

                <!-- 项目名称 -->
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">项目名称 *</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
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
                        <option value="track" {{ old('event_type') == 'track' ? 'selected' : '' }}>径赛</option>
                        <option value="field" {{ old('event_type') == 'field' ? 'selected' : '' }}>田赛</option>
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
                        <option value="男" {{ old('gender') == '男' ? 'selected' : '' }}>男</option>
                        <option value="女" {{ old('gender') == '女' ? 'selected' : '' }}>女</option>
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
                           value="{{ old('max_participants') }}"
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
                           value="{{ old('avg_time', 5) }}"
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

                <!-- 示例提示 -->
                <div class="alert alert-info mt-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm">
                        <p class="font-semibold">常见项目示例：</p>
                        <p><strong>径赛：</strong>100米、200米、400米、800米、1500米、4×100米接力</p>
                        <p><strong>田赛：</strong>跳高、跳远、三级跳远、铅球、铁饼、标枪</p>
                    </div>
                </div>

                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('events.index') }}" class="btn btn-ghost">取消</a>
                    <button type="submit" class="btn btn-primary">添加项目</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
