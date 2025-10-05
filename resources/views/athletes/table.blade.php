@if($athletes->isNotEmpty())
    <div class="overflow-x-auto">
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th class="w-12">#</th>
                    <th class="w-20">编号</th>
                    <th>姓名</th>
                    <th class="w-16">性别</th>
                    <th>班级</th>
                    <th>报名项目</th>
                    <th class="w-40">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($athletes as $index => $athlete)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($athlete->number)
                                <span class="badge badge-primary">{{ $athlete->number }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="font-semibold">{{ $athlete->name }}</td>
                        <td>
                            @if($athlete->gender === '男')
                                <span class="badge badge-info">男</span>
                            @else
                                <span class="badge badge-secondary">女</span>
                            @endif
                        </td>
                        <td>{{ $athlete->klass->name }}</td>
                        <td>
                            @php
                                // 获取该运动员的报名项目
                                $athleteEvents = \App\Models\Event::whereIn('id', function($query) use ($athlete) {
                                    $query->select('competition_events.event_id')
                                        ->from('athlete_competition_events')
                                        ->join('competition_events', 'athlete_competition_events.competition_event_id', '=', 'competition_events.id')
                                        ->where('athlete_competition_events.athlete_id', $athlete->id);
                                })->get();
                            @endphp
                            @if($athleteEvents->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($athleteEvents as $event)
                                        <span class="badge badge-sm badge-outline">{{ $event->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-sm">未报名项目</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <a href="{{ route('competitions.athletes.edit', [$competition, $athlete]) }}" 
                                   class="btn btn-sm btn-ghost">
                                    编辑
                                </a>
                                <form action="{{ route('competitions.athletes.destroy', [$competition, $athlete]) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('确定要删除该运动员吗？');">
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
@else
    <div class="text-center py-12">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="text-gray-500 text-sm">暂无运动员信息</p>
        <p class="text-gray-400 text-xs mt-1">点击右上角"添加运动员"按钮开始添加</p>
    </div>
@endif
