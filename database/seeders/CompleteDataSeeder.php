<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Athlete;
use App\Models\Competition;
use App\Models\Event;
use App\Models\User;
use App\Models\Grade;
use App\Models\Klass;
use App\Models\CompetitionEvent;
use App\Models\AthleteCompetitionEvent;
use Illuminate\Support\Facades\Hash;

class CompleteDataSeeder extends Seeder
{

    public function run(): void
    {
        echo "\n🧹 清理旧数据...\n";

        // 清理旧数据

        User::truncate();
        AthleteCompetitionEvent::truncate();
        Athlete::truncate();
        Klass::truncate();
        Grade::truncate();
        CompetitionEvent::truncate();
        Competition::truncate();
        Event::truncate();

        // 创建管理员
        echo "\n🛠️ 创建管理员账号...\n";
        $user = User::create([
            'name' => '管理员',
            'email' => env('ADMIN_EMAIL'),
            'password' => Hash::make(env('ADMIN_PASSWORD'))
        ]);

        // 创建测试用户
        echo "\n👤 创建测试用户...\n";
        User::create([
            'name' => '测试用户',
            'email' => 'user@example.com',
            'password' => Hash::make('password')
        ]);

        echo "\n🌱 正在导入比赛项目数据...\n";

        // 径赛项目
        $trackEvents = [
            ['name' => "100米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 5, 'max_participants' => 6],
            ['name' => "100米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 5, 'max_participants' => 6],
            ['name' => "200米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 5, 'max_participants' => 6],
            ['name' => "200米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 5, 'max_participants' => 6],
            ['name' => "400米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 5, 'max_participants' => 6],
            ['name' => "400米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 5, 'max_participants' => 6],
            ['name' => "800米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 10, 'max_participants' => 6],
            ['name' => "800米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 10, 'max_participants' => 6],
            ['name' => "1500米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 15, 'max_participants' => 6],
            ['name' => "1500米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 25, 'max_participants' => 6],
            ['name' => "4*300米", 'gender' => "男", 'event_type' => "track", 'avg_time' => 20, 'max_participants' => 24],
            ['name' => "4*300米", 'gender' => "女", 'event_type' => "track", 'avg_time' => 20, 'max_participants' => 24],
        ];

        // 田赛项目
        $fieldEvents = [
            ['name' => "立定三级跳", 'gender' => "男", 'event_type' => "field", 'avg_time' => 20, 'max_participants' => 99],
            ['name' => "立定三级跳", 'gender' => "女", 'event_type' => "field", 'avg_time' => 20, 'max_participants' => 99],
            ['name' => "跳高", 'gender' => "男", 'event_type' => "field", 'avg_time' => 20, 'max_participants' => 99],
            ['name' => "跳高", 'gender' => "女", 'event_type' => "field", 'avg_time' => 20, 'max_participants' => 99],
            ['name' => "跳远", 'gender' => "男", 'event_type' => "field", 'avg_time' => 30, 'max_participants' => 99],
            ['name' => "跳远", 'gender' => "女", 'event_type' => "field", 'avg_time' => 30, 'max_participants' => 99],
            ['name' => "前掷实心球", 'gender' => "男", 'event_type' => "field", 'avg_time' => 30, 'max_participants' => 99],
            ['name' => "前掷实心球", 'gender' => "女", 'event_type' => "field", 'avg_time' => 30, 'max_participants' => 99],
        ];

        // 插入数据

        $users = User::all();
        foreach ($users as $u) {
            foreach (array_merge($trackEvents, $fieldEvents) as $event) {
                $u->events()->create($event);
            }
        }

        echo "✅ " . Event::count() . " 个比赛项目已成功导入。\n";
    }
}
