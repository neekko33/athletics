<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            // 男子径赛项目
            ['name' => '100米', 'event_type' => 'track', 'gender' => '男', 'max_participants' => null, 'avg_time' => 5],
            ['name' => '200米', 'event_type' => 'track', 'gender' => '男', 'max_participants' => null, 'avg_time' => 5],
            ['name' => '400米', 'event_type' => 'track', 'gender' => '男', 'max_participants' => null, 'avg_time' => 8],
            ['name' => '800米', 'event_type' => 'track', 'gender' => '男', 'max_participants' => null, 'avg_time' => 10],
            ['name' => '1500米', 'event_type' => 'track', 'gender' => '男', 'max_participants' => null, 'avg_time' => 15],
            ['name' => '110米栏', 'event_type' => 'track', 'gender' => '男', 'max_participants' => null, 'avg_time' => 6],
            ['name' => '4×100米接力', 'event_type' => 'track', 'gender' => '男', 'max_participants' => 4, 'avg_time' => 6],
            ['name' => '4×400米接力', 'event_type' => 'track', 'gender' => '男', 'max_participants' => 4, 'avg_time' => 12],
            
            // 女子径赛项目
            ['name' => '100米', 'event_type' => 'track', 'gender' => '女', 'max_participants' => null, 'avg_time' => 5],
            ['name' => '200米', 'event_type' => 'track', 'gender' => '女', 'max_participants' => null, 'avg_time' => 5],
            ['name' => '400米', 'event_type' => 'track', 'gender' => '女', 'max_participants' => null, 'avg_time' => 8],
            ['name' => '800米', 'event_type' => 'track', 'gender' => '女', 'max_participants' => null, 'avg_time' => 10],
            ['name' => '1500米', 'event_type' => 'track', 'gender' => '女', 'max_participants' => null, 'avg_time' => 15],
            ['name' => '100米栏', 'event_type' => 'track', 'gender' => '女', 'max_participants' => null, 'avg_time' => 6],
            ['name' => '4×100米接力', 'event_type' => 'track', 'gender' => '女', 'max_participants' => 4, 'avg_time' => 6],
            ['name' => '4×400米接力', 'event_type' => 'track', 'gender' => '女', 'max_participants' => 4, 'avg_time' => 12],
            
            // 男子田赛项目
            ['name' => '跳高', 'event_type' => 'field', 'gender' => '男', 'max_participants' => null, 'avg_time' => 30],
            ['name' => '跳远', 'event_type' => 'field', 'gender' => '男', 'max_participants' => null, 'avg_time' => 20],
            ['name' => '三级跳远', 'event_type' => 'field', 'gender' => '男', 'max_participants' => null, 'avg_time' => 20],
            ['name' => '铅球', 'event_type' => 'field', 'gender' => '男', 'max_participants' => null, 'avg_time' => 20],
            ['name' => '铁饼', 'event_type' => 'field', 'gender' => '男', 'max_participants' => null, 'avg_time' => 20],
            ['name' => '标枪', 'event_type' => 'field', 'gender' => '男', 'max_participants' => null, 'avg_time' => 20],
            
            // 女子田赛项目
            ['name' => '跳高', 'event_type' => 'field', 'gender' => '女', 'max_participants' => null, 'avg_time' => 30],
            ['name' => '跳远', 'event_type' => 'field', 'gender' => '女', 'max_participants' => null, 'avg_time' => 20],
            ['name' => '三级跳远', 'event_type' => 'field', 'gender' => '女', 'max_participants' => null, 'avg_time' => 20],
            ['name' => '铅球', 'event_type' => 'field', 'gender' => '女', 'max_participants' => null, 'avg_time' => 20],
            ['name' => '铁饼', 'event_type' => 'field', 'gender' => '女', 'max_participants' => null, 'avg_time' => 20],
            ['name' => '标枪', 'event_type' => 'field', 'gender' => '女', 'max_participants' => null, 'avg_time' => 20],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
