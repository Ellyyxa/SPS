<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    public function index()
    {
        // Student hanya nampak notification sendiri
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }


    public function create()
    {
        // Admin pilih pelajar
        $students = User::where('role', 'student')->get();

        return view('notifications.create', compact('students'));
    }


    public function store(Request $request)
{
    $request->validate([
        'title' => 'required',
        'message' => 'required',
        'user_id' => 'nullable',
    ]);

    // Hantar kepada semua pelajar
    if ($request->user_id == 'all') {

        $students = User::where('role', 'student')->get();

        foreach ($students as $student) {

            Notification::create([
                'admin_id' => auth()->id(),
                'user_id' => $student->id,
                'title' => $request->title,
                'message' => $request->message,
            ]);

        }

    } else {

        Notification::create([
            'admin_id' => auth()->id(),
            'user_id' => $request->user_id,
            'title' => $request->title,
            'message' => $request->message,
        ]);

    }

    return redirect()->route('notifications.index')
        ->with('success', 'Notification sent successfully.');
}


    public function show(Notification $notification)
    {
        //
    }


    public function edit(Notification $notification)
    {
        //
    }


    public function update(Request $request, Notification $notification)
    {
        //
    }


    public function destroy(Notification $notification)
    {
        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }
}