<?php

namespace App\Http\Controllers;

use App\Models\Competition;

class ScheduleController extends Controller
{
    public function index(Competition $competition)
    {
        return view('schedules.index', $competition);
    }
}
