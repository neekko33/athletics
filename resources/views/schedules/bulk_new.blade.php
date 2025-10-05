@extends('layouts.app')

@section('title', '批量添加日程')

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
        <h1 class="font-bold text-2xl mb-6">批量添加日程</h1>

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($groupedHeats->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg mb-4">没有可批量安排的项目</p>
                <p class="text-gray-400 text-sm mb-6">所有分组都已安排时间</p>
                <a href="{{ route('competitions.schedules.index', $competition) }}"
                   class="btn btn-primary">
                    返回日程列表
                </a>
            </div>
        @else
            <form action="{{ route('competitions.schedules.bulk-create', $competition) }}"
                  method="POST"
                  id="bulk-schedule-form"
                  class="contents">
                @csrf

                <!-- 选择比赛分组 -->
                <div class="my-5">
                    <label for="group_select" class="label">
                        <span class="label-text">选择比赛分组</span>
                    </label>
                    <select name="group_select"
                            id="group-select"
                            class="select select-bordered w-full mt-2"
                            required>
                        <option value="">-- 请选择 --</option>
                        @foreach($groupedHeats as $key => $item)
                            @php
                                $data = $item['data'];
                                $heats = $item['heats'];
                            @endphp
                            <option value="{{ $data['grade_id'] }}|{{ $data['event_id'] }}|{{ $data['gender'] }}|{{ $data['avg_time'] }}"
                                    data-heats-count="{{ $heats->count() }}"
                                    data-avg-time="{{ $data['avg_time'] }}">
                                {{ $data['grade_name'] }} - {{ $data['event_name'] }} ({{ $data['gender'] }}) - {{ $heats->count() }} 个分组
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="grade_id" id="grade_id">
                    <input type="hidden" name="event_id" id="event_id">
                    <input type="hidden" name="gender" id="gender">
                    <input type="hidden" name="avg_time" id="avg_time">
                </div>

                <!-- 将要安排的分组预览 -->
                <div id="heats-preview" class="my-5 hidden">
                    <label class="label">
                        <span class="label-text">将要安排的分组</span>
                    </label>
                    <div class="bg-base-200 p-3 rounded-lg max-h-48 overflow-y-auto mt-2">
                        @foreach($groupedHeats as $key => $item)
                            @php
                                $data = $item['data'];
                                $heats = $item['heats'];
                                $groupId = "{$data['grade_id']}_{$data['event_id']}_{$data['gender']}";
                            @endphp
                            <div class="heats-list hidden" data-group-id="{{ $groupId }}">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($heats as $heat)
                                        <span class="badge badge-outline badge-sm">
                                            第{{ $heat->heat_number }}组
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 开始日期 -->
                <div class="my-5">
                    <label for="start_date" class="label">
                        <span class="label-text">开始日期</span>
                    </label>
                    <input type="date"
                           name="start_date"
                           id="start_date"
                           value="{{ request('date') ?? $competition->start_date->format('Y-m-d') }}"
                           min="{{ $competition->start_date->format('Y-m-d') }}"
                           max="{{ $competition->end_date->format('Y-m-d') }}"
                           class="input input-bordered block mt-2 w-full"
                           required>
                </div>

                <!-- 开始时间 -->
                <div class="my-5">
                    <label for="start_time" class="label">
                        <span class="label-text">开始时间</span>
                    </label>
                    <input type="time"
                           name="start_time"
                           id="start_time"
                           value="08:00"
                           class="input input-bordered block mt-2 w-full"
                           required>
                </div>

                <!-- 时间预览 -->
                <div id="time-preview" class="my-5 hidden">
                    <label class="label">
                        <span class="label-text">时间预览</span>
                    </label>
                    <div class="bg-blue-50 text-blue-700 px-4 py-3 rounded-lg mt-2 text-sm">
                        将安排 <strong id="preview-count">0</strong> 个分组，
                        从 <strong id="preview-start"></strong> 开始，
                        预计到 <strong id="preview-end"></strong> 结束
                        <span class="text-xs opacity-75">(按项目平均用时 <span id="preview-avg-time"></span> 分钟自动计算)</span>
                    </div>
                </div>

                <!-- 场地 -->
                <div class="my-5">
                    <label for="venue" class="label">
                        <span class="label-text">场地</span>
                    </label>
                    <input type="text"
                           name="venue"
                           id="venue"
                           placeholder="例如：田径场"
                           class="input input-bordered block mt-2 w-full">
                </div>

                <!-- 备注 -->
                <div class="my-5">
                    <label for="notes" class="label">
                        <span class="label-text">备注</span>
                    </label>
                    <textarea name="notes"
                              id="notes"
                              placeholder="其他说明信息"
                              class="textarea textarea-bordered block mt-2 w-full"
                              rows="3"></textarea>
                </div>

                <!-- 提交按钮 -->
                <div class="inline mt-6">
                    <button type="submit" class="btn btn-primary mr-2">批量添加</button>
                    <a href="{{ route('competitions.schedules.index', $competition) }}"
                       class="btn">取消</a>
                </div>
            </form>

            <script>
                function initBulkScheduleForm() {
                    const groupSelect = document.getElementById('group-select');
                    const heatsPreview = document.getElementById('heats-preview');
                    const timePreview = document.getElementById('time-preview');
                    const dateInput = document.getElementById('start_date');
                    const timeInput = document.getElementById('start_time');
                    const gradeIdInput = document.getElementById('grade_id');
                    const eventIdInput = document.getElementById('event_id');
                    const genderInput = document.getElementById('gender');
                    const avgTimeInput = document.getElementById('avg_time');
                    const form = document.getElementById('bulk-schedule-form');

                    if (!groupSelect || !form) {
                        console.log('Form elements not found, skipping initialization');
                        return;
                    }

                    console.log('Initializing bulk schedule form');

                    function updatePreview() {
                        const selectedOption = groupSelect.options[groupSelect.selectedIndex];

                        if (selectedOption.value) {
                            // 解析选中的值: grade_id|event_id|gender|avg_time
                            const [gradeId, eventId, gender, avgTime] = selectedOption.value.split('|');
                            const heatsCount = parseInt(selectedOption.dataset.heatsCount);

                            // 设置隐藏字段
                            gradeIdInput.value = gradeId;
                            eventIdInput.value = eventId;
                            genderInput.value = gender;
                            avgTimeInput.value = avgTime;

                            console.log('Hidden fields set:', {
                                grade_id: gradeIdInput.value,
                                event_id: eventIdInput.value,
                                gender: genderInput.value,
                                avg_time: avgTimeInput.value
                            });

                            // 显示分组列表
                            heatsPreview.classList.remove('hidden');
                            document.querySelectorAll('.heats-list').forEach(list => {
                                list.classList.add('hidden');
                            });
                            const groupId = `${gradeId}_${eventId}_${gender}`;
                            const targetList = document.querySelector(`[data-group-id="${groupId}"]`);
                            if (targetList) {
                                targetList.classList.remove('hidden');
                            }

                            // 更新时间预览
                            updateTimePreview(heatsCount, parseInt(avgTime));
                        } else {
                            heatsPreview.classList.add('hidden');
                            timePreview.classList.add('hidden');
                            gradeIdInput.value = '';
                            eventIdInput.value = '';
                            genderInput.value = '';
                            avgTimeInput.value = '';
                        }
                    }

                    function updateTimePreview(heatsCount, avgTime) {
                        if (!dateInput.value || !timeInput.value || !heatsCount || !avgTime) {
                            timePreview.classList.add('hidden');
                            return;
                        }

                        const startDateTime = new Date(`${dateInput.value}T${timeInput.value}`);
                        const totalMinutes = (heatsCount - 1) * avgTime + avgTime;
                        const endDateTime = new Date(startDateTime.getTime() + totalMinutes * 60000);

                        document.getElementById('preview-count').textContent = heatsCount;
                        document.getElementById('preview-start').textContent = startDateTime.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' });
                        document.getElementById('preview-end').textContent = endDateTime.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' });
                        document.getElementById('preview-avg-time').textContent = avgTime;

                        timePreview.classList.remove('hidden');
                    }

                    // 表单提交前验证
                    form.addEventListener('submit', function(e) {
                        if (!gradeIdInput.value || !eventIdInput.value || !genderInput.value || !avgTimeInput.value) {
                            e.preventDefault();
                            alert('请先选择比赛分组');
                            return false;
                        }
                        console.log('Form submitting with:', {
                            grade_id: gradeIdInput.value,
                            event_id: eventIdInput.value,
                            gender: genderInput.value,
                            avg_time: avgTimeInput.value
                        });
                    });

                    groupSelect.addEventListener('change', updatePreview);
                    dateInput.addEventListener('change', () => {
                        const selectedOption = groupSelect.options[groupSelect.selectedIndex];
                        if (selectedOption.value) {
                            const heatsCount = parseInt(selectedOption.dataset.heatsCount);
                            const avgTime = parseInt(selectedOption.dataset.avgTime);
                            updateTimePreview(heatsCount, avgTime);
                        }
                    });
                    timeInput.addEventListener('change', () => {
                        const selectedOption = groupSelect.options[groupSelect.selectedIndex];
                        if (selectedOption.value) {
                            const heatsCount = parseInt(selectedOption.dataset.heatsCount);
                            const avgTime = parseInt(selectedOption.dataset.avgTime);
                            updateTimePreview(heatsCount, avgTime);
                        }
                    });

                    // 页面加载时，如果有选中的值，立即显示预览
                    if (groupSelect.value) {
                        updatePreview();
                    }
                }

                // 支持 Turbo 和传统页面加载
                document.addEventListener('DOMContentLoaded', initBulkScheduleForm);
                document.addEventListener('turbo:load', initBulkScheduleForm);
                document.addEventListener('turbo:render', initBulkScheduleForm);
            </script>
        @endif
    </div>
</div>
@endsection
