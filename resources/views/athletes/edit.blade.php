@extends('layouts.app')

@section('title', '编辑运动员')

@section('content')
<div class="container mx-auto max-w-3xl">
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
            <h2 class="card-title text-2xl mb-4">编辑运动员</h2>

            <form action="{{ route('competitions.athletes.update', [$competition, $athlete]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">姓名 *</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $athlete->name) }}"
                           class="input input-bordered @error('name') input-error @enderror"
                           placeholder="请输入运动员姓名" required>
                    @error('name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">性别 *</span>
                    </label>
                    <select name="gender" id="genderSelect"
                            class="select select-bordered @error('gender') select-error @enderror" required>
                        <option value="">请选择性别</option>
                        <option value="男" {{ old('gender', $athlete->gender) === '男' ? 'selected' : '' }}>男</option>
                        <option value="女" {{ old('gender', $athlete->gender) === '女' ? 'selected' : '' }}>女</option>
                    </select>
                    @error('gender')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">班级 *</span>
                    </label>
                    <input type="text" name="klass_name" value="{{ old('klass_name', $athlete->klass->name) }}"
                           class="input input-bordered @error('klass_name') input-error @enderror"
                           placeholder="例如: 1班" required>
                    <label class="label">
                        <span class="label-text-alt text-gray-500">当前年级: {{ $grade->name }}</span>
                    </label>
                    @error('klass_name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                @php
                    $athleteEventIds = old('event_ids');
                    if (!$athleteEventIds) {
                        $athleteEventIds = \DB::table('athlete_competition_events')
                            ->join('competition_events', 'athlete_competition_events.competition_event_id', '=', 'competition_events.id')
                            ->where('athlete_competition_events.athlete_id', $athlete->id)
                            ->pluck('competition_events.event_id')
                            ->toArray();
                    }
                @endphp

                <div class="form-control w-full mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">报名项目</span>
                    </label>
                    <div class="border border-base-300 rounded-lg p-4">
                        <div id="noGenderNotice" class="alert alert-warning" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>请先选择性别以查看可报名项目</span>
                        </div>

                        <!-- 径赛项目 -->
                        <div id="trackSection" style="display: none;" class="mb-4">
                            <h3 class="text-sm font-medium mb-2">径赛项目</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                @foreach($events->where('event_type', 'track') as $event)
                                    <label class="flex items-center event-item" data-gender="{{ $event->gender }}" data-type="track">
                                        <input type="checkbox" name="event_ids[]" value="{{ $event->id }}"
                                               class="checkbox checkbox-sm mr-2"
                                               {{ in_array($event->id, $athleteEventIds) ? 'checked' : '' }}>
                                        <span class="text-sm">{{ $event->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- 田赛项目 -->
                        <div id="fieldSection" style="display: none;">
                            <h3 class="text-sm font-medium mb-2">田赛项目</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                @foreach($events->where('event_type', 'field') as $event)
                                    <label class="flex items-center event-item" data-gender="{{ $event->gender }}" data-type="field">
                                        <input type="checkbox" name="event_ids[]" value="{{ $event->id }}"
                                               class="checkbox checkbox-sm mr-2"
                                               {{ in_array($event->id, $athleteEventIds) ? 'checked' : '' }}>
                                        <span class="text-sm">{{ $event->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('competitions.athletes.index', $competition) }}" class="btn btn-ghost">取消</a>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const genderSelect = document.getElementById('genderSelect');
    const noGenderNotice = document.getElementById('noGenderNotice');
    const trackSection = document.getElementById('trackSection');
    const fieldSection = document.getElementById('fieldSection');
    const eventItems = document.querySelectorAll('.event-item');

    function filterEvents() {
        const selectedGender = genderSelect.value;

        if (!selectedGender) {
            noGenderNotice.style.display = 'flex';
            trackSection.style.display = 'none';
            fieldSection.style.display = 'none';
            eventItems.forEach(item => {
                item.style.display = 'none';
                // 不禁用，保留已选状态
            });
            return;
        }

        noGenderNotice.style.display = 'none';

        let hasTrack = false;
        let hasField = false;

        eventItems.forEach(item => {
            const eventGender = item.dataset.gender;
            const eventType = item.dataset.type;

            if (eventGender === selectedGender) {
                item.style.display = 'flex';

                if (eventType === 'track') hasTrack = true;
                if (eventType === 'field') hasField = true;
            } else {
                item.style.display = 'none';
            }
        });

        trackSection.style.display = hasTrack ? 'block' : 'none';
        fieldSection.style.display = hasField ? 'block' : 'none';
    }

    genderSelect.addEventListener('change', filterEvents);

    // 页面加载时执行一次
    filterEvents();
});
</script>
@endsection
