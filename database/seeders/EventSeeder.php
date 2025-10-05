<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
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
            ['name' => "跳高", 'gender' => "男", 'event_type' => "field", 'avg_time' => 20, 'max_participants' => 99],
            ['name' => "跳高", 'gender' => "女", 'event_type' => "field", 'avg_time' => 20, 'max_participants' => 99],
            ['name' => "跳远", 'gender' => "男", 'event_type' => "field", 'avg_time' => 15, 'max_participants' => 99],
            ['name' => "跳远", 'gender' => "女", 'event_type' => "field", 'avg_time' => 15, 'max_participants' => 99]
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
