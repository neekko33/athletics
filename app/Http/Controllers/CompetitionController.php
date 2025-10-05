<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Event;
use App\Services\ScheduleBookService;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    public function index()
    {
        $competitions = Competition::orderBy('start_date', 'desc')->get();
        return view('competitions.index', compact('competitions'));
    }

    public function create()
    {
        return view('competitions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'daily_start_time' => 'required',
            'daily_end_time' => 'required',
            'track_lanes' => 'required|integer|min:1|max:10',
        ]);

        $competition = Competition::create($validated);

        return redirect()->route('competitions.show', $competition)
            ->with('success', '运动会创建成功');
    }

    public function show(Competition $competition)
    {
        $competition->load([
            'grades.klasses.athletes',
            'competitionEvents.event',
            'competitionEvents.heats.schedule'
        ]);

        // 统计数据
        $stats = [
            'total_athletes' => $competition->grades->sum(function ($grade) {
                return $grade->klasses->sum(function ($klass) {
                    return $klass->athletes->count();
                });
            }),
            'total_events' => $competition->competitionEvents->count(),
            'total_heats' => $competition->competitionEvents->sum(function ($ce) {
                return $ce->heats->count();
            }),
            'scheduled_heats' => $competition->competitionEvents->sum(function ($ce) {
                return $ce->heats->filter(function ($heat) {
                    return $heat->schedule !== null;
                })->count();
            }),
        ];

        return view('competitions.show', compact('competition', 'stats'));
    }

    public function edit(Competition $competition)
    {
        return view('competitions.edit', compact('competition'));
    }

    public function update(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'daily_start_time' => 'required',
            'daily_end_time' => 'required',
            'track_lanes' => 'required|integer|min:1|max:10',
        ]);

        $competition->update($validated);

        return redirect()->route('competitions.show', $competition)
            ->with('success', '运动会更新成功');
    }

    public function destroy(Competition $competition)
    {
        $competition->delete();

        return redirect()->route('competitions.index')
            ->with('success', '运动会删除成功');
    }

    /**
     * 生成秩序册
     */
    public function generateScheduleBook(Competition $competition, ScheduleBookService $service)
    {
        try {
            $fileName = $service->generate($competition);

            $filePath = storage_path('app/public/schedules/' . $fileName);

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', '生成秩序册失败：' . $e->getMessage());
        }
    }
}
