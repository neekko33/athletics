<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Athlete;
use App\Models\Competition;
use App\Models\Event;
use App\Models\User;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\CompetitionEvent;
use App\Models\AthleteCompetitionEvent;

class CompleteDataSeeder extends Seeder
{
    private function numberToChinese($number)
    {
        $chineseNumbers = [
            0 => "零", 1 => "一", 2 => "二", 3 => "三", 4 => "四",
            5 => "五", 6 => "六", 7 => "七", 8 => "八", 9 => "九",
            10 => "十", 11 => "十一", 12 => "十二"
        ];

        return $chineseNumbers[$number] ?? (string)$number;
    }

    public function run(): void
    {
        echo "\n🧹 清理旧数据...\n";

        // 清理旧数据
        AthleteCompetitionEvent::truncate();
        Athlete::truncate();
        Klass::truncate();
        Grade::truncate();
        CompetitionEvent::truncate();
        Competition::truncate();
        Event::truncate();
        User::where('email', 'neekko33@gmail.com')->delete();

        echo "\n🌱 正在导入比赛项目数据...\n";

        // 径赛项目
        $trackEvents = [
            ['name' => "100米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 5, 'max_participants' => 6],
            ['name' => "100米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 5, 'max_participants' => 6],
            ['name' => "200米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 6, 'max_participants' => 6],
            ['name' => "200米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 6, 'max_participants' => 6],
            ['name' => "400米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 8, 'max_participants' => 6],
            ['name' => "400米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 8, 'max_participants' => 6],
            ['name' => "800米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 10, 'max_participants' => 6],
            ['name' => "800米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 10, 'max_participants' => 6],
            ['name' => "1500米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 12, 'max_participants' => 6],
            ['name' => "1500米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 12, 'max_participants' => 6],
            ['name' => "4*100米接力", 'gender' => "男", 'event_type' => "track", 'avg_time' => 8, 'max_participants' => 24],
            ['name' => "4*100米接力", 'gender' => "女", 'event_type' => "track", 'avg_time' => 8, 'max_participants' => 24],
        ];

        // 田赛项目
        $fieldEvents = [
            ['name' => "跳高", 'gender' => "男", 'event_type' => "field", 'avg_time' => 20, 'max_participants' => 99],
            ['name' => "跳高", 'gender' => "女", 'event_type' => "field", 'avg_time' => 20, 'max_participants' => 99],
            ['name' => "跳远", 'gender' => "男", 'event_type' => "field", 'avg_time' => 15, 'max_participants' => 99],
            ['name' => "跳远", 'gender' => "女", 'event_type' => "field", 'avg_time' => 15, 'max_participants' => 99],
        ];

        // 插入数据
        foreach (array_merge($trackEvents, $fieldEvents) as $event) {
            Event::create($event);
        }

        echo "✅ " . Event::count() . " 个比赛项目已成功导入。\n";

        // 插入测试用户
        User::create([
            'name' => 'Test User',
            'email' => 'neekko33@gmail.com',
            'password' => bcrypt('password'),
        ]);
        echo "✅ 测试用户已创建，邮箱：neekko33@gmail.com，密码：password\n";

        // 创建测试运动会
        echo "\n🏃 创建测试运动会数据...\n";
        $competition = Competition::create([
            'name' => '2025年秋季运动会',
            'start_date' => '2025-10-15',
            'end_date' => '2025-10-17',
            'track_lanes' => 6,
        ]);
        echo "✅ 运动会创建成功: {$competition->name}\n";

        // 创建年级和班级
        echo "\n📚 创建年级和班级...\n";
        $gradesData = [
            ['name' => '七年级', 'order' => 1, 'class_count' => 5],
            ['name' => '八年级', 'order' => 2, 'class_count' => 6],
        ];

        $grades = [];
        foreach ($gradesData as $gradeData) {
            $grade = $competition->grades()->create([
                'name' => $gradeData['name'],
                'order' => $gradeData['order'],
            ]);
            $grades[$gradeData['name']] = $grade;
            echo "  ✓ 创建年级: {$grade->name}\n";

            for ($i = 1; $i <= $gradeData['class_count']; $i++) {
                $klass = $grade->klasses()->create([
                    'name' => $this->numberToChinese($i) . '班',
                    'order' => $i,
                ]);
                echo "    ✓ 创建班级: {$grade->name} {$klass->name}\n";
            }
        }

        // 获取所有项目（创建 CompetitionEvent）
        $allEvents = Event::all();
        $competitionEvents = [];
        foreach ($allEvents as $event) {
            $ce = $competition->competitionEvents()->firstOrCreate([
                'event_id' => $event->id,
            ]);
            $competitionEvents["{$event->name}_{$event->gender}"] = $ce;
        }

        echo "\n👥 导入真实运动员数据...\n";

        // 七年级男子组数据
        $grade7MaleAthletes = [
            ['name' => "王勋然", 'klass' => "一班", 'events' => ["100米", "400米"]],
            ['name' => "刘天昊", 'klass' => "二班", 'events' => ["100米", "800米"]],
            ['name' => "刘世雨", 'klass' => "三班", 'events' => ["100米", "200米"]],
            ['name' => "高志国", 'klass' => "四班", 'events' => ["100米", "800米"]],
            ['name' => "李帅威", 'klass' => "五班", 'events' => ["100米", "200米"]],
            ['name' => "李子默", 'klass' => "三班", 'events' => ["100米", "跳高"]],
            ['name' => "翟聪颖", 'klass' => "二班", 'events' => ["100米"]],
            ['name' => "闫肃", 'klass' => "一班", 'events' => ["100米", "跳远"]],
            ['name' => "杨毅哲", 'klass' => "四班", 'events' => ["100米", "1500米"]],
            ['name' => "梁世博", 'klass' => "五班", 'events' => ["100米", "200米"]],
            ['name' => "郝子淳", 'klass' => "四班", 'events' => ["200米", "跳远"]],
            ['name' => "王勋涛", 'klass' => "一班", 'events' => ["200米", "800米"]],
            ['name' => "李晓淼", 'klass' => "二班", 'events' => ["200米", "400米"]],
            ['name' => "姚家乐", 'klass' => "四班", 'events' => ["200米", "跳远"]],
            ['name' => "赵宗鑫", 'klass' => "一班", 'events' => ["200米", "1500米"]],
            ['name' => "王若天", 'klass' => "三班", 'events' => ["200米", "1500米"]],
            ['name' => "杨子杰", 'klass' => "二班", 'events' => ["200米"]],
            ['name' => "李天佑", 'klass' => "四班", 'events' => ["400米", "跳高"]],
            ['name' => "张子赫", 'klass' => "三班", 'events' => ["400米", "800米"]],
            ['name' => "张舒恺", 'klass' => "五班", 'events' => ["400米", "跳远"]],
            ['name' => "靳文士", 'klass' => "四班", 'events' => ["400米"]],
            ['name' => "赵富恒", 'klass' => "五班", 'events' => ["400米", "跳远"]],
            ['name' => "胡雪涛", 'klass' => "二班", 'events' => ["400米", "1500米"]],
            ['name' => "尹国轩", 'klass' => "一班", 'events' => ["400米", "1500米"]],
            ['name' => "张道聪", 'klass' => "一班", 'events' => ["800米", "跳远"]],
            ['name' => "马子孑", 'klass' => "二班", 'events' => ["800米", "1500米"]],
            ['name' => "秦开明", 'klass' => "五班", 'events' => ["800米", "1500米"]],
            ['name' => "张自言", 'klass' => "五班", 'events' => ["800米", "1500米"]],
            ['name' => "丁灿", 'klass' => "三班", 'events' => ["1500米", "跳远"]],
            ['name' => "高有诺", 'klass' => "三班", 'events' => ["跳高", "跳远"]],
        ];

        // 七年级女子组数据
        $grade7FemaleAthletes = [
            ['name' => "董雨若", 'klass' => "一班", 'events' => ["100米"]],
            ['name' => "赵飞雪", 'klass' => "二班", 'events' => ["100米", "200米"]],
            ['name' => "王晓雅", 'klass' => "三班", 'events' => ["100米", "跳高"]],
            ['name' => "王子玥", 'klass' => "四班", 'events' => ["100米"]],
            ['name' => "曹可欣", 'klass' => "五班", 'events' => ["100米"]],
            ['name' => "王宇喧", 'klass' => "四班", 'events' => ["100米"]],
            ['name' => "张淑妍", 'klass' => "五班", 'events' => ["100米", "跳高"]],
            ['name' => "管刘烨", 'klass' => "一班", 'events' => ["100米", "800米"]],
            ['name' => "吴婉如", 'klass' => "二班", 'events' => ["100米", "400米"]],
            ['name' => "王念念", 'klass' => "三班", 'events' => ["100米", "跳高"]],
            ['name' => "曹晓婉", 'klass' => "三班", 'events' => ["200米", "跳远"]],
            ['name' => "刘子涵", 'klass' => "四班", 'events' => ["200米"]],
            ['name' => "段萌萌", 'klass' => "五班", 'events' => ["200米", "400米"]],
            ['name' => "冯闰涵", 'klass' => "一班", 'events' => ["200米", "跳高"]],
            ['name' => "苏雪", 'klass' => "二班", 'events' => ["200米", "800米"]],
            ['name' => "刘洋", 'klass' => "三班", 'events' => ["200米", "800米"]],
            ['name' => "高含香", 'klass' => "五班", 'events' => ["200米", "400米"]],
            ['name' => "张优", 'klass' => "四班", 'events' => ["200米"]],
            ['name' => "鲍佳琦", 'klass' => "三班", 'events' => ["400米", "800米"]],
            ['name' => "车俊雅", 'klass' => "一班", 'events' => ["400米", "800米"]],
            ['name' => "任沁怡", 'klass' => "四班", 'events' => ["400米", "800米"]],
            ['name' => "张惜诺", 'klass' => "一班", 'events' => ["400米", "跳远"]],
            ['name' => "袁子姿", 'klass' => "四班", 'events' => ["400米", "800米"]],
            ['name' => "房念思", 'klass' => "二班", 'events' => ["400米", "跳高"]],
            ['name' => "张若熙", 'klass' => "三班", 'events' => ["400米", "跳远"]],
            ['name' => "方依冉", 'klass' => "二班", 'events' => ["800米", "1500米"]],
            ['name' => "张淑鑫", 'klass' => "五班", 'events' => ["800米"]],
            ['name' => "谷余乐", 'klass' => "五班", 'events' => ["800米", "跳高"]],
            ['name' => "王语晴", 'klass' => "二班", 'events' => ["1500米", "跳远"]],
            ['name' => "李冰冰", 'klass' => "一班", 'events' => ["跳高", "跳远"]],
        ];

        // 八年级男子组数据
        $grade8MaleAthletes = [
            // 100米参赛运动员
            ['name' => "张晨光", 'klass' => "一班", 'events' => ["100米", "跳高"]],
            ['name' => "张佳博", 'klass' => "二班", 'events' => ["100米", "400米"]],
            ['name' => "巴瑞康", 'klass' => "三班", 'events' => ["100米", "400米"]],
            ['name' => "刘焱康", 'klass' => "四班", 'events' => ["100米", "200米"]],
            ['name' => "薛王博", 'klass' => "五班", 'events' => ["100米", "1500米"]],
            ['name' => "黄海诺", 'klass' => "六班", 'events' => ["100米", "200米"]],
            ['name' => "刘天齐", 'klass' => "六班", 'events' => ["100米", "200米"]],
            ['name' => "谷正荣", 'klass' => "一班", 'events' => ["100米", "跳高", "跳远"]],
            ['name' => "刘硕", 'klass' => "四班", 'events' => ["100米", "800米"]],
            ['name' => "李首彦", 'klass' => "三班", 'events' => ["100米", "跳远"]],
            ['name' => "于佳辉", 'klass' => "五班", 'events' => ["100米", "200米"]],
            ['name' => "后文强", 'klass' => "四班", 'events' => ["200米", "跳远"]],
            ['name' => "郑叶硕", 'klass' => "三班", 'events' => ["200米", "跳高"]],
            ['name' => "武冰璨", 'klass' => "二班", 'events' => ["200米", "跳远"]],
            ['name' => "董成挡", 'klass' => "一班", 'events' => ["200米", "800米"]],
            ['name' => "张晗旭", 'klass' => "一班", 'events' => ["200米", "跳远"]],
            ['name' => "任远通", 'klass' => "二班", 'events' => ["200米", "800米"]],
            ['name' => "张国宇", 'klass' => "三班", 'events' => ["200米", "400米"]],
            ['name' => "郭子翔", 'klass' => "一班", 'events' => ["400米", "800米"]],
            ['name' => "邢丙衡", 'klass' => "六班", 'events' => ["400米", "跳远"]],
            ['name' => "李浩然", 'klass' => "五班", 'events' => ["400米", "800米"]],
            ['name' => "张百超", 'klass' => "四班", 'events' => ["400米", "800米"]],
            ['name' => "李浩鑫", 'klass' => "五班", 'events' => ["400米", "800米"]],
            ['name' => "田宪哲", 'klass' => "二班", 'events' => ["400米", "跳远"]],
            ['name' => "马国苗", 'klass' => "一班", 'events' => ["400米", "跳远"]],
            ['name' => "尹泽浩", 'klass' => "六班", 'events' => ["400米"]],
            ['name' => "马晓赫", 'klass' => "二班", 'events' => ["800米", "跳高"]],
            ['name' => "张扬", 'klass' => "三班", 'events' => ["800米", "跳高"]],
            ['name' => "王位东", 'klass' => "六班", 'events' => ["800米", "跳高"]],
            ['name' => "张帆", 'klass' => "二班", 'events' => ["1500米", "跳高"]],
            ['name' => "孟泽熙", 'klass' => "三班", 'events' => ["1500米", "跳远"]],
            ['name' => "刘德治", 'klass' => "四班", 'events' => ["1500米", "跳高"]],
            ['name' => "崔镇烁", 'klass' => "五班", 'events' => ["1500米", "跳高"]],
            ['name' => "尹哲浩", 'klass' => "六班", 'events' => ["1500米"]],
            ['name' => "李朝旭", 'klass' => "五班", 'events' => ["跳高", "跳远"]],
            ['name' => "杨永健", 'klass' => "六班", 'events' => ["跳高", "跳远"]],
            ['name' => "刘记越", 'klass' => "四班", 'events' => ["跳高", "跳远"]],
        ];

        // 八年级女子组数据
        $grade8FemaleAthletes = [
            ['name' => "谷言", 'klass' => "一班", 'events' => ["100米", "跳远"]],
            ['name' => "葛晓艺", 'klass' => "二班", 'events' => ["100米", "1500米"]],
            ['name' => "方梦瑶", 'klass' => "三班", 'events' => ["100米", "800米"]],
            ['name' => "李珊珊", 'klass' => "四班", 'events' => ["100米", "200米"]],
            ['name' => "肖雪妍", 'klass' => "五班", 'events' => ["100米", "200米"]],
            ['name' => "王依诺", 'klass' => "六班", 'events' => ["100米", "200米"]],
            ['name' => "任焓雪", 'klass' => "一班", 'events' => ["100米", "800米"]],
            ['name' => "田晓涵", 'klass' => "二班", 'events' => ["100米", "200米"]],
            ['name' => "李梦鑫", 'klass' => "三班", 'events' => ["100米", "跳远"]],
            ['name' => "刘晨雪", 'klass' => "四班", 'events' => ["100米", "跳远"]],
            ['name' => "郝琪琪", 'klass' => "五班", 'events' => ["100米", "跳高"]],
            ['name' => "贾梦佳", 'klass' => "六班", 'events' => ["100米", "400米"]],
            ['name' => "闫研", 'klass' => "一班", 'events' => ["200米", "400米"]],
            ['name' => "刘抒情", 'klass' => "二班", 'events' => ["200米", "跳远"]],
            ['name' => "胡傲婷", 'klass' => "三班", 'events' => ["200米", "跳高"]],
            ['name' => "梁佳依", 'klass' => "五班", 'events' => ["200米", "800米"]],
            ['name' => "夏怡欣", 'klass' => "六班", 'events' => ["200米", "400米"]],
            ['name' => "李雨昕", 'klass' => "一班", 'events' => ["200米", "跳远"]],
            ['name' => "杜佳琪", 'klass' => "三班", 'events' => ["200米", "400米"]],
            ['name' => "李紫诺", 'klass' => "四班", 'events' => ["200米", "400米"]],
            ['name' => "孙晨妍", 'klass' => "五班", 'events' => ["400米", "跳远"]],
            ['name' => "刘毅云", 'klass' => "一班", 'events' => ["400米", "800米"]],
            ['name' => "王语馨", 'klass' => "二班", 'events' => ["400米", "跳高"]],
            ['name' => "王梦晗", 'klass' => "三班", 'events' => ["400米", "跳远"]],
            ['name' => "孙雪妍", 'klass' => "二班", 'events' => ["400米", "800米"]],
            ['name' => "周灿", 'klass' => "五班", 'events' => ["400米", "1500米"]],
            ['name' => "张梦琪", 'klass' => "四班", 'events' => ["800米", "跳高"]],
            ['name' => "董艺一", 'klass' => "六班", 'events' => ["800米", "跳远"]],
            ['name' => "李盈秀", 'klass' => "二班", 'events' => ["800米", "跳远"]],
            ['name' => "张雪娜", 'klass' => "三班", 'events' => ["1500米", "跳高"]],
            ['name' => "刘慧轲", 'klass' => "四班", 'events' => ["1500米", "跳高"]],
            ['name' => "支冰洋", 'klass' => "五班", 'events' => ["1500米", "跳远"]],
            ['name' => "程欣怡", 'klass' => "六班", 'events' => ["1500米", "跳高"]],
            ['name' => "谢安然", 'klass' => "一班", 'events' => ["1500米", "跳高"]],
            ['name' => "高慧茹", 'klass' => "六班", 'events' => ["跳高", "跳远"]],
            ['name' => "王亚茹", 'klass' => "四班", 'events' => ["跳远"]],
        ];

        // 创建七年级运动员
        echo "\n  📖 创建七年级运动员...\n";
        $grade7 = $grades['七年级'];

        // 男子组
        foreach ($grade7MaleAthletes as $athleteData) {
            $klass = $grade7->klasses()->where('name', $athleteData['klass'])->first();
            if (!$klass) continue;

            $athlete = $klass->athletes()->create([
                'name' => $athleteData['name'],
                'gender' => '男',
            ]);

            // 创建报名记录
            foreach ($athleteData['events'] as $eventName) {
                $ceKey = "{$eventName}_男";
                $ce = $competitionEvents[$ceKey] ?? null;
                if ($ce) {
                    $athlete->athleteCompetitionEvents()->create([
                        'competition_event_id' => $ce->id,
                    ]);
                }
            }
        }

        // 女子组
        foreach ($grade7FemaleAthletes as $athleteData) {
            $klass = $grade7->klasses()->where('name', $athleteData['klass'])->first();
            if (!$klass) continue;

            $athlete = $klass->athletes()->create([
                'name' => $athleteData['name'],
                'gender' => '女',
            ]);

            // 创建报名记录
            foreach ($athleteData['events'] as $eventName) {
                $ceKey = "{$eventName}_女";
                $ce = $competitionEvents[$ceKey] ?? null;
                if ($ce) {
                    $athlete->athleteCompetitionEvents()->create([
                        'competition_event_id' => $ce->id,
                    ]);
                }
            }
        }

        echo "  ✅ 七年级运动员创建完成\n";

        // 创建八年级运动员
        echo "\n  📚 创建八年级运动员...\n";
        $grade8 = $grades['八年级'];

        // 男子组
        foreach ($grade8MaleAthletes as $athleteData) {
            $klass = $grade8->klasses()->where('name', $athleteData['klass'])->first();
            if (!$klass) continue;

            $athlete = $klass->athletes()->create([
                'name' => $athleteData['name'],
                'gender' => '男',
            ]);

            // 创建报名记录
            foreach ($athleteData['events'] as $eventName) {
                $ceKey = "{$eventName}_男";
                $ce = $competitionEvents[$ceKey] ?? null;
                if ($ce) {
                    $athlete->athleteCompetitionEvents()->create([
                        'competition_event_id' => $ce->id,
                    ]);
                }
            }
        }

        // 女子组
        foreach ($grade8FemaleAthletes as $athleteData) {
            $klass = $grade8->klasses()->where('name', $athleteData['klass'])->first();
            if (!$klass) continue;

            $athlete = $klass->athletes()->create([
                'name' => $athleteData['name'],
                'gender' => '女',
            ]);

            // 创建报名记录
            foreach ($athleteData['events'] as $eventName) {
                $ceKey = "{$eventName}_女";
                $ce = $competitionEvents[$ceKey] ?? null;
                if ($ce) {
                    $athlete->athleteCompetitionEvents()->create([
                        'competition_event_id' => $ce->id,
                    ]);
                }
            }
        }

        echo "  ✅ 八年级运动员创建完成\n";

        // 统计信息
        echo "\n📊 数据统计：\n";
        echo "  运动会: " . Competition::count() . " 个\n";
        echo "  年级: " . Grade::count() . " 个\n";
        echo "  班级: " . Klass::count() . " 个\n";
        echo "  运动员: " . Athlete::count() . " 人\n";
        echo "    - 男生: " . Athlete::where('gender', '男')->count() . " 人\n";
        echo "    - 女生: " . Athlete::where('gender', '女')->count() . " 人\n";
        echo "  比赛项目: " . Event::count() . " 个\n";
        echo "  报名记录: " . AthleteCompetitionEvent::count() . " 条\n";
        echo "  参赛项目: " . CompetitionEvent::count() . " 个\n";

        // 按年级统计
        echo "\n  七年级：\n";
        echo "    男生: " . Athlete::whereHas('klass.grade', function($q) {
            $q->where('name', '七年级');
        })->where('gender', '男')->count() . " 人\n";
        echo "    女生: " . Athlete::whereHas('klass.grade', function($q) {
            $q->where('name', '七年级');
        })->where('gender', '女')->count() . " 人\n";

        echo "  八年级：\n";
        echo "    男生: " . Athlete::whereHas('klass.grade', function($q) {
            $q->where('name', '八年级');
        })->where('gender', '男')->count() . " 人\n";
        echo "    女生: " . Athlete::whereHas('klass.grade', function($q) {
            $q->where('name', '八年级');
        })->where('gender', '女')->count() . " 人\n";

        echo "\n✨ 真实数据导入完成！\n";
        echo str_repeat("=", 60) . "\n";
    }
}
