<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Athlete;
use App\Models\Grade;
use App\Models\Event;
use App\Models\Klass;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class AthleteController extends Controller
{
    public function index(Competition $competition)
    {
        $grades = $competition->grades()
            ->with([
                'klasses' => function($query) {
                    $query->with(['athletes' => function($q) {
                        $q->with('klass');
                    }]);
                }
            ])
            ->get();
        
        return view('athletes.index', compact('competition', 'grades'));
    }

    public function create(Request $request, Competition $competition)
    {
        $gradeId = $request->query('grade_id');
        $grade = $competition->grades()->findOrFail($gradeId);
        $events = Event::all();
        
        return view('athletes.create', compact('competition', 'grade', 'events'));
    }

    public function store(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:男,女',
            'grade_id' => 'required|exists:grades,id',
            'klass_name' => 'required|string|max:255',
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'exists:events,id',
        ]);

        try {
            DB::beginTransaction();

            // 查找年级
            $grade = $competition->grades()->findOrFail($validated['grade_id']);
            
            // 查找或创建班级
            $klassName = $validated['klass_name'];
            $order = 0;
            
            // 尝试从班级名称提取数字作为order
            if (preg_match('/(\d+)/', $klassName, $matches)) {
                $order = (int)$matches[1];
            } else {
                $order = $grade->klasses()->max('order') + 1;
            }
            
            $klass = $grade->klasses()->firstOrCreate(
                ['name' => $klassName],
                ['order' => $order]
            );

            // 创建运动员
            $athlete = $klass->athletes()->create([
                'name' => $validated['name'],
                'gender' => $validated['gender'],
            ]);

            // 处理报名项目
            if (!empty($validated['event_ids'])) {
                foreach ($validated['event_ids'] as $eventId) {
                    // 查找或创建CompetitionEvent
                    $competitionEvent = $competition->competitionEvents()->firstOrCreate([
                        'event_id' => $eventId
                    ]);
                    
                    // 创建运动员与比赛项目的关联
                    $athlete->athleteCompetitionEvents()->create([
                        'competition_event_id' => $competitionEvent->id
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('competitions.athletes.index', $competition)
                ->with('success', '运动员添加成功');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', '操作失败：' . $e->getMessage());
        }
    }

    public function edit(Competition $competition, Athlete $athlete)
    {
        $grade = $athlete->klass->grade;
        $events = Event::all();
        
        return view('athletes.edit', compact('competition', 'athlete', 'grade', 'events'));
    }

    public function update(Request $request, Competition $competition, Athlete $athlete)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:男,女',
            'klass_name' => 'required|string|max:255',
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'exists:events,id',
        ]);

        try {
            DB::beginTransaction();

            $grade = $athlete->klass->grade;
            $klassName = $validated['klass_name'];
            
            // 查找或创建班级
            $order = 0;
            if (preg_match('/(\d+)/', $klassName, $matches)) {
                $order = (int)$matches[1];
            } else {
                $order = $grade->klasses()->max('order') + 1;
            }
            
            $klass = $grade->klasses()->firstOrCreate(
                ['name' => $klassName],
                ['order' => $order]
            );

            // 更新运动员基本信息
            $athlete->update([
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'klass_id' => $klass->id,
            ]);

            // 更新报名项目
            // 删除旧的关联
            $athlete->athleteCompetitionEvents()->delete();
            
            // 创建新的关联
            if (!empty($validated['event_ids'])) {
                foreach ($validated['event_ids'] as $eventId) {
                    $competitionEvent = $competition->competitionEvents()->firstOrCreate([
                        'event_id' => $eventId
                    ]);
                    
                    $athlete->athleteCompetitionEvents()->create([
                        'competition_event_id' => $competitionEvent->id
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('competitions.athletes.index', $competition)
                ->with('success', '运动员信息更新成功');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', '操作失败：' . $e->getMessage());
        }
    }

    public function destroy(Competition $competition, Athlete $athlete)
    {
        $athlete->delete();

        return redirect()->route('competitions.athletes.index', $competition)
            ->with('success', '运动员已删除');
    }

    public function generateNumbers(Competition $competition)
    {
        // 按年级 -> 班级 -> 性别(男->女)排序，生成编号
        $athletes = $competition->grades()
            ->with(['klasses' => function($query) {
                $query->with('athletes')->orderBy('order');
            }])
            ->orderBy('order')
            ->get()
            ->flatMap(function($grade) {
                return $grade->klasses->flatMap(function($klass) {
                    // 先男生，后女生
                    return $klass->athletes->sortBy(function($athlete) {
                        return $athlete->gender === '男' ? 0 : 1;
                    });
                });
            });

        $athletes->each(function($athlete, $index) {
            $athlete->update(['number' => sprintf('%03d', $index + 1)]);
        });

        return redirect()->route('competitions.athletes.index', $competition)
            ->with('success', "运动员编号生成成功！共生成 {$athletes->count()} 个编号");
    }

    public function import(Request $request, Competition $competition)
    {
        if (!$request->hasFile('file')) {
            return redirect()->route('competitions.athletes.index', $competition)
                ->with('error', '请选择要导入的Excel文件');
        }

        $file = $request->file('file');
        
        // 添加日志用于调试
        \Log::info('Starting import', [
            'competition_id' => $competition->id,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize()
        ]);

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            
            \Log::info('Excel loaded', ['highest_row' => $highestRow]);
            
            $importedCount = 0;
            $errors = [];

            // 从第2行开始读取（第1行是标题）
            for ($row = 2; $row <= $highestRow; $row++) {
                $gradeName = trim($worksheet->getCell("A{$row}")->getValue() ?? '');
                $klassName = trim($worksheet->getCell("B{$row}")->getValue() ?? '');
                $athleteName = trim($worksheet->getCell("C{$row}")->getValue() ?? '');
                $gender = trim($worksheet->getCell("D{$row}")->getValue() ?? '');
                $eventsStr = trim($worksheet->getCell("E{$row}")->getValue() ?? '');

                // 跳过空行
                if (empty($gradeName) && empty($athleteName)) {
                    continue;
                }

                // 查找年级
                $grade = $competition->grades()->where('name', $gradeName)->first();
                if (!$grade) {
                    $errors[] = "第{$row}行: 找不到年级 '{$gradeName}'";
                    continue;
                }

                // 查找或创建班级
                $order = 0;
                if (preg_match('/(\d+)/', $klassName, $matches)) {
                    $order = (int)$matches[1];
                } else {
                    $order = $grade->klasses()->max('order') + 1;
                }
                
                $klass = $grade->klasses()->firstOrCreate(
                    ['name' => $klassName],
                    ['order' => $order]
                );

                // 创建运动员
                $athlete = $klass->athletes()->create([
                    'name' => $athleteName,
                    'gender' => $gender,
                ]);

                if ($athlete) {
                    // 处理报名项目
                    if (!empty($eventsStr)) {
                        $eventNames = preg_split('/[,，、]/', $eventsStr);
                        foreach ($eventNames as $eventName) {
                            $eventName = trim($eventName);
                            if (empty($eventName)) continue;

                            $event = Event::where('name', $eventName)
                                ->where('gender', $gender)
                                ->first();
                            
                            if ($event) {
                                $competitionEvent = $competition->competitionEvents()
                                    ->firstOrCreate(['event_id' => $event->id]);
                                
                                $athlete->athleteCompetitionEvents()->create([
                                    'competition_event_id' => $competitionEvent->id
                                ]);
                            } else {
                                $errors[] = "第{$row}行: 找不到项目 '{$eventName}'（性别：{$gender}）";
                            }
                        }
                    }
                    $importedCount++;
                }
            }

            \Log::info('Import completed', [
                'imported' => $importedCount,
                'errors' => count($errors)
            ]);

            if (!empty($errors)) {
                $errorMsg = "导入完成，成功 {$importedCount} 条，失败 " . count($errors) . " 条。";
                if (count($errors) <= 10) {
                    $errorMsg .= " 错误详情：" . implode('; ', $errors);
                }
                return redirect()->route('competitions.athletes.index', $competition)
                    ->with('warning', $errorMsg);
            }

            return redirect()->route('competitions.athletes.index', $competition)
                ->with('success', "成功导入 {$importedCount} 名运动员");

        } catch (\Exception $e) {
            \Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('competitions.athletes.index', $competition)
                ->with('error', '导入失败: ' . $e->getMessage());
        }
    }

    public function downloadTemplate(Competition $competition)
    {
        $grades = $competition->grades()->orderBy('order')->pluck('name')->toArray();

        // 创建CSV内容
        $csvData = [];
        $csvData[] = ['年级', '班级', '姓名', '性别', '报名项目'];
        $csvData[] = ['# 请删除此说明行', '填写示例如下', '↓↓↓', '', ''];

        // 添加示例数据
        if (!empty($grades)) {
            $firstGrade = $grades[0];
            $csvData[] = [$firstGrade, '1班', '张三', '男', '100米,跳远'];
            $csvData[] = [$firstGrade, '1班', '李四', '女', '100米,200米'];
            $csvData[] = [$firstGrade, '2班', '王五', '男', '400米,跳高'];

            if (count($grades) > 1) {
                $secondGrade = $grades[1];
                $csvData[] = [$secondGrade, '1班', '赵六', '女', '200米'];
            }
        } else {
            $csvData[] = ['一年级', '1班', '张三', '男', '100米,跳远'];
            $csvData[] = ['一年级', '1班', '李四', '女', '100米,200米'];
            $csvData[] = ['二年级', '2班', '王五', '男', '400米'];
        }

        // 生成CSV文件内容
        $output = fopen('php://temp', 'r+');
        
        // 添加BOM以支持Excel正确识别UTF-8
        fputs($output, "\xEF\xBB\xBF");
        
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        $filename = "运动员导入模板_{$competition->name}_" . date('Ymd') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
