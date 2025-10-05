@extends('layouts.app')

@section('title', '编辑运动会')

@section('content')
<div class="container mx-auto max-w-2xl">
    <h2 class="text-3xl font-bold mb-6">编辑运动会</h2>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <form action="{{ route('competitions.update', $competition) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">运动会名称 *</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $competition->name) }}"
                           class="input input-bordered w-full @error('name') input-error @enderror" required>
                    @error('name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">开始日期 *</span>
                        </label>
                        <input type="date" name="start_date" value="{{ old('start_date', $competition->start_date->format('Y-m-d')) }}"
                               class="input input-bordered w-full @error('start_date') input-error @enderror" required>
                        @error('start_date')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">结束日期 *</span>
                        </label>
                        <input type="date" name="end_date" value="{{ old('end_date', $competition->end_date->format('Y-m-d')) }}"
                               class="input input-bordered w-full @error('end_date') input-error @enderror" required>
                        @error('end_date')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text font-semibold">每日开始时间 *</span>
                        </label>
                        <input type="time" name="daily_start_time" value="{{ old('daily_start_time', $competition->daily_start_time) }}"
                               class="input input-bordered w-full @error('daily_start_time') input-error @enderror" required>
                        @error('daily_start_time')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control w-full">
                        <label class="label">
                            <span class="label-text-alt font-semibold">每日结束时间 *</span>
                        </label>
                        <input type="time" name="daily_end_time" value="{{ old('daily_end_time', $competition->daily_end_time) }}"
                               class="input input-bordered w-full @error('daily_end_time') input-error @enderror" required>
                        @error('daily_end_time')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                </div>

                <div class="form-control w-full mt-4">
                    <label class="label">
                        <span class="label-text font-semibold">径赛赛道数 *</span>
                    </label>
                    <input type="number" name="track_lanes" value="{{ old('track_lanes', $competition->track_lanes) }}"
                           min="1" max="10"
                           class="input input-bordered w-full @error('track_lanes') input-error @enderror" required>
                    @error('track_lanes')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('competitions.index') }}" class="btn btn-ghost">取消</a>
                    <button type="submit" class="btn btn-primary">保存更改</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
