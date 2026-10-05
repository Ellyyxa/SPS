<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $rows = Notification::with(['user:id,name,student_id', 'admin:id,name'])
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($inner) => $inner->where('title', 'like', '%'.$request->string('search').'%')->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$request->string('search').'%')->orWhere('student_id', 'like', '%'.$request->string('search').'%'))))
            ->when($request->type === 'broadcast', fn ($query) => $query->whereNotNull('broadcast_id'))
            ->when($request->type === 'individual', fn ($query) => $query->whereNull('broadcast_id'))
            ->latest()
            ->get();

        $groups = $rows->groupBy(fn (Notification $notification) => $notification->broadcast_id ? 'broadcast:'.$notification->broadcast_id : 'notification:'.$notification->id)
            ->map(function ($group) {
                $notification = $group->first();
                $notification->recipient_label = $notification->broadcast_id ? 'All Students' : trim($notification->user->name.' · '.$notification->user->student_id);
                $notification->recipient_count = $group->count();
                return $notification;
            })->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        $notifications = new LengthAwarePaginator($groups->forPage($page, $perPage)->values(), $groups->count(), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $students = User::where('role', 'student')->orderBy('name')->get(['id', 'name', 'student_id']);
        return view('admin.notifications.create', compact('students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'recipient_type' => ['required', Rule::in(['student', 'all'])],
            'user_id' => [Rule::requiredIf($request->recipient_type === 'student'), 'nullable', 'integer'],
        ]);

        $students = $data['recipient_type'] === 'all'
            ? User::where('role', 'student')->get(['id'])
            : User::where('role', 'student')->whereKey($data['user_id'])->get(['id']);

        if ($students->isEmpty()) {
            return back()->withErrors(['user_id' => 'Select a valid student recipient.'])->withInput();
        }

        $broadcastId = $data['recipient_type'] === 'all' ? (string) Str::uuid() : null;
        DB::transaction(function () use ($students, $request, $data, $broadcastId) {
            foreach ($students as $student) {
                Notification::create(['admin_id' => $request->user()->id, 'user_id' => $student->id, 'broadcast_id' => $broadcastId, 'title' => $data['title'], 'message' => $data['message']]);
            }
        });

        return redirect()->route('admin.notifications.index')->with('success', $broadcastId ? 'Notification sent to all students.' : 'Notification sent successfully.');
    }
}
