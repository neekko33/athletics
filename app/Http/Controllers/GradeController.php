<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Competition $competition)
    {
        $grades = $competition->grades()->with('klasses.athletes')->get();
        return view('grades.index', compact('competition', 'grades'));
    }

    public function create(Competition $competition)
    {
        return view('grades.create', compact('competition'));
    }

    public function store(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // 自动设置order为当前最大order + 1
        $validated['order'] = $competition->grades()->max('order') + 1;
        $validated['competition_id'] = $competition->id;

        Grade::create($validated);

        return redirect()->route('competitions.grades.index', $competition)
            ->with('success', '年级创建成功');
    }

    public function edit(Competition $competition, Grade $grade)
    {
        return view('grades.edit', compact('competition', 'grade'));
    }

    public function update(Request $request, Competition $competition, Grade $grade)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $grade->update($validated);

        return redirect()->route('competitions.grades.index', $competition)
            ->with('success', '年级更新成功');
    }

    public function destroy(Competition $competition, Grade $grade)
    {
        $grade->delete();

        return redirect()->route('competitions.grades.index', $competition)
            ->with('success', '年级删除成功');
    }
}
