<div class="mb-8 border-b">
    @if ($schedulesByDate->isNotEmpty())
        @foreach ($schedulesByDate as $date => $daySchedules)
            <div class="mb-8">
                <h4 class="text-2xl font-bold mb-4 flex items-center">
                    <span class="badge badge-primary badge-lg mr-3">
                        {{ \Carbon\Carbon::parse($date)->format('m月d日') }}
                    </span>
                    <span class="text-gray-600 text-base font-normal">
                        星期{{ ['日', '一', '二', '三', '四', '五', '六'][\Carbon\Carbon::parse($date)->dayOfWeek] }}
                    </span>
                    <span class="ml-auto text-sm font-normal text-gray-500 mr-4">
                        共 {{ $daySchedules->count() }} 场比赛
                    </span>
                    <button type="submit" class="btn btn-error btn-dash mr-4"
                        wire:click="clearTodaySchedules({{ json_encode($date) }}, 'track')" wire:confirm="确定删除当天所有日程吗？">
                        清空当天日程
                    </button>
                </h4>
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>时间</th>
                            <th>比赛项目</th>
                            <th>性别</th>
                            <th>分组数量</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daySchedules as $eventName => $schedule)
                            <tr class="hover">
                                <td class="whitespace-nowrap">
                                    <div class="flex gap-1">
                                        <div class="font-medium">
                                            {{ $schedule['scheduled_at']->format('H:i') }}
                                        </div>
                                        @if ($schedule['end_at'])
                                            <div class="text-sm text-gray-500">
                                                ~ {{ $schedule['end_at']->format('H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $schedule['event']->name }}</div>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $schedule['event']->gender }}</div>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $schedule['schedules']->count() }}</div>
                                </td>
                                <td>
                                    <div class="flex space-x-2">
                                        <button type="button" class="btn btn-sm btn-error" wire:confirm="确定删除该日程吗？"
                                            wire:click="deleteSchedule({{ $schedule['event']->id }})">
                                            删除
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg mb-4">暂无日程安排</p>
            <p class="text-gray-400 text-sm mb-6">请先生成径赛分组，然后为每个分组安排时间</p>
            <a href="{{ route('competitions.schedules.bulk-new', $competition) }}?type=track"
                class="btn btn-secondary">
                添加比赛
            </a>
        </div>
    @endif

    @if (count($heatsWithoutSchedule) > 0)
        <div class="mt-8 border-t pt-6">
            <h4 class="font-bold text-xl mb-4">未安排的比赛项目</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($heatsWithoutSchedule as $eventName => $heats)
                    <div class="card bg-gray-50 border border-gray-200">
                        <div class="card-body p-4">
                            <h5 class="card-title text-base">
                                {{ $eventName }}
                            </h5>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ count($heats) }} 个分组
                            </p>
                            <button class="btn btn-primary btn-sm"
                                wire:click="openModal('{{ $eventName }}')">设置日程</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($isModalOpen)
        <dialog id="my_modal" class="modal modal-open">
            <div class="modal-box">
                <div>
                    <h1 class="font-bold text-2xl mb-6">添加日程 - {{ $scheduleTitle }}</h1>

                    <!-- 开始日期 -->
                    <div class="my-5">
                        <label for="start_date" class="label">
                            <span class="label-text">开始日期</span>
                        </label>
                        <input type="date" name="start_date" id="start_date" wire:model="scheduleStartDate"
                            min="{{ $competition->start_date->format('Y-m-d') }}"
                            max="{{ $competition->end_date->format('Y-m-d') }}"
                            class="input input-bordered block mt-2 w-full" required>
                    </div>

                    <!-- 开始时间 -->
                    <div class="my-5">
                        <label for="start_time" class="label">
                            <span class="label-text">开始时间</span>
                        </label>
                        <input type="time" name="start_time" id="start_time" wire:model.blur="scheduleStartTime"
                            class="input input-bordered block mt-2 w-full" required>
                    </div>

                    <!-- 时间预览 -->
                    <div id="time-preview" class="my-5">
                        <label class="label">
                            <span class="label-text">时间预览</span>
                        </label>
                        <div class="bg-blue-50 text-blue-700 px-4 py-3 rounded-lg mt-2 text-sm">
                            将安排 <strong
                                id="preview-count">{{ $scheduleTitle ? count($heatsWithoutSchedule[$scheduleTitle]) : 0 }}</strong>
                            个分组，
                            从 <strong id="preview-start">{{ $scheduleStartTime }}</strong> 开始，
                            预计到 <strong id="preview-end"> {{ $scheduleEndTime }}</strong> 结束
                            <span class="text-xs opacity-75">(按项目平均用时 <span
                                    id="preview-avg-time">{{ $scheduleAvgTime }}</span> 分钟自动计算)</span>
                        </div>
                    </div>

                    <!-- 备注 -->
                    <div class="my-5">
                        <label for="notes" class="label">
                            <span class="label-text">备注</span>
                        </label>
                        <textarea name="notes" id="notes" placeholder="其他说明信息" class="textarea textarea-bordered block mt-2 w-full"
                            rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-action">
                    <button class="btn btn-primary" wire:click="saveSchedule">保存</button>
                    <button class="btn" wire:click="$set('isModalOpen', false)">取消</button>
                </div>
            </div>
        </dialog>
    @endif
</div>
