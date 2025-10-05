<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('event_type')->orderBy('gender')->orderBy('name')->get();
        $trackEvents = $events->where('event_type', 'track');
        $fieldEvents = $events->where('event_type', 'field');
        
        return view('events.index', compact('trackEvents', 'fieldEvents'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|in:track,field',
            'gender' => 'required|in:男,女',
            'max_participants' => 'nullable|integer|min:1',
            'avg_time' => 'nullable|integer|min:1',
        ]);

        Event::create($validated);

        return redirect()->route('events.index')
            ->with('success', '运动项目创建成功');
    }

    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|in:track,field',
            'gender' => 'required|in:男,女',
            'max_participants' => 'nullable|integer|min:1',
            'avg_time' => 'nullable|integer|min:1',
        ]);

        $event->update($validated);

        return redirect()->route('events.index')
            ->with('success', '运动项目更新成功');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', '运动项目删除成功');
    }
}
