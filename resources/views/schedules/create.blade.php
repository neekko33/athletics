@extends('layouts.app')

@section('title', '添加日程')

@section('content')
<div class="container mx-auto max-w-2xl">
    <!-- 步骤条 -->
    <div class="mb-6">
        <ul class="steps w-full">
            <li class="step step-primary">创建运动会</li>
            <li class="step step-primary">参赛年级</li>
            <li class="step step-primary">运动员报名</li>
            <li class="step step-primary">径赛分组</li>
            <li class="step step-primary">日程安排</li>
            <li class="step">总览</li>
        </ul>
    </div>

    <div class="bg-white p-6 rounded-md shadow-md">
        <h1 class="font-bold text-2xl mb-6">添加日程</h1>

        <form action="{{ route('competitions.schedules.store', $competition) }}" 
              method="POST">
            @csrf

            <!-- 选择分组 -->
            <div class="my-5">
                <label for="heat_id" class="label">
                    <span class="label-text">选择分组 <span class="text-red-500">*</span></span>
                </label>
                <select name="heat_id" 
                        id="heat_id" 
                        class="select select-bordered w-full @error('heat_id') border-error @enderror"
                        required>
                    <option value="">-- 请选择 --</option>
                    @foreach($availableHeats as $availableHeat)
                        <option value="{{ $availableHeat->id }}" 
                                {{ (old('heat_id', $heat?->id) == $availableHeat->id) ? 'selected' : '' }}>
                            {{ $availableHeat->grade->name }} - 
                            {{ $availableHeat->competitionEvent->event->name }} - 
                            第 {{ $availableHeat->heat_number }} 组 
                            ({{ $availableHeat->competitionEvent->event->gender }})
                        </option>
                    @endforeach
                </select>
                @error('heat_id')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <!-- 开始时间 -->
            <div class="my-5">
                <label for="scheduled_at" class="label">
                    <span class="label-text">开始时间 <span class="text-red-500">*</span></span>
                </label>
                <input type="datetime-local" 
                       name="scheduled_at" 
                       id="scheduled_at"
                       value="{{ old('scheduled_at') }}"
                       class="input input-bordered w-full @error('scheduled_at') border-error @enderror"
                       required>
                @error('scheduled_at')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <!-- 结束时间 -->
            <div class="my-5">
                <label for="end_at" class="label">
                    <span class="label-text">结束时间</span>
                </label>
                <input type="datetime-local" 
                       name="end_at" 
                       id="end_at"
                       value="{{ old('end_at') }}"
                       class="input input-bordered w-full @error('end_at') border-error @enderror">
                @error('end_at')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <!-- 场地 -->
            <div class="my-5">
                <label for="venue" class="label">
                    <span class="label-text">场地</span>
                </label>
                <input type="text" 
                       name="venue" 
                       id="venue"
                       value="{{ old('venue') }}"
                       placeholder="例如：田径场"
                       class="input input-bordered w-full @error('venue') border-error @enderror">
                @error('venue')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <!-- 备注 -->
            <div class="my-5">
                <label for="notes" class="label">
                    <span class="label-text">备注</span>
                </label>
                <textarea name="notes" 
                          id="notes"
                          placeholder="其他说明信息"
                          class="textarea textarea-bordered w-full @error('notes') border-error @enderror" 
                          rows="3">{{ old('notes') }}</textarea>
                @error('notes')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <!-- 提交按钮 -->
            <div class="inline mt-6">
                <button type="submit" class="btn btn-primary mr-2">添加日程</button>
                <a href="{{ route('competitions.schedules.index', $competition) }}" 
                   class="btn">取消</a>
            </div>
        </form>
    </div>
</div>
@endsection
