<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
    {
        $events =  Notification::get();
        return view('events.index', ['events' => $events]);
    }

    public function getById($id)
    {
        $event =  Notification::with('camera')->findOrFail($id);
        return response()->json($event, 200);
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $status = $request->status == True ? 1 : 0;
        $timestamp = Carbon::createFromTimestamp($request->time);

        $data = [
            'start_error_time' => $timestamp,
            'url' => $request->url,
            'status' => $status,
            'camera_id' => $request->camera_id,
        ];

        $event = Notification::create($data);

        response()->json($event, 200);
    }

    public function edit($eventId)
    {
        $event = Notification::findOrFail($eventId);

        return view('events.edit', [
            'event' => $event,
        ]);
    }

    public function update(Request $request)
    {
        $event = Notification::where('camera_id', '=', $request->camera_id)
            ->orderBy('created_at', 'desc')
            ->first();
        $timestamp = Carbon::createFromTimestamp($request->time);

        $data = [
            'end_error_time' => $timestamp,
            'status' => 0,
        ];

        $event->update($data);

        return response()->json("Successfully update event", 200);
    }

    public function destroy($eventId)
    {
        $event = Notification::findOrFail($eventId);
        $event->delete();

        return redirect('/events')->with('status', 'Event Delete Successfully');
    }
}