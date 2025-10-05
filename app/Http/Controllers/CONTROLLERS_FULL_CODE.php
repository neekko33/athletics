<?php
/**
 * 完整的控制器实现代码
 * 本文件包含所有控制器的完整代码，用于复制到对应的控制器文件中
 */

// ============================================
// GradeController.php
// ============================================
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
            'order' => 'required|integer',
        ]);

        $validated['competition_id'] = $competition->id;
        Grade::create($validated);

        return redirect()->route('competitions.grades.index', $competition)
            ->with('success', '年级添加成功');
    }

    public function edit(Competition $competition, Grade $grade)
    {
        return view('grades.edit', compact('competition', 'grade'));
    }

    public function update(Request $request, Competition $competition, Grade $grade)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'required|integer',
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

