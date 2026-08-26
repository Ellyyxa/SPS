@extends('layouts.student')

@section('content')
    @php
        $moodFaces = ['Happy' => '😊', 'Neutral' => '😐', 'Sad' => '😢', 'Stress' => '😰', 'Angry' => '😠'];
        $moodFace = $moodFaces[$todayMood?->mood] ?? '🙂';
    @endphp

    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Student Productivity System</p>
            <h1 class="student-page-title mt-1">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="mt-2 text-slate-600">Here is your productivity snapshot for today.</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="student-button">+ Add Task</a>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="student-card p-5"><p class="text-sm font-bold text-slate-500">Total tasks</p><p class="mt-2 text-4xl font-extrabold text-indigo-950">{{ $totalTasks }}</p></div>
        <div class="student-card p-5"><p class="text-sm font-bold text-slate-500">Completed</p><p class="mt-2 text-4xl font-extrabold text-emerald-600">{{ $completedTasks }}</p></div>
        <div class="student-card p-5"><p class="text-sm font-bold text-slate-500">Pending</p><p class="mt-2 text-4xl font-extrabold text-amber-500">{{ $pendingTasks }}</p></div>
        <div class="student-card p-5"><p class="text-sm font-bold text-slate-500">Due today</p><p class="mt-2 text-4xl font-extrabold text-rose-500">{{ $todayTasks->count() }}</p></div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <article class="student-card overflow-hidden">
            <div class="student-card-header flex items-center justify-between"><span>Today’s Mood</span><span class="text-2xl">{{ $moodFace }}</span></div>
            <div class="flex items-center gap-4 p-6"><span class="text-6xl">{{ $moodFace }}</span><div><p class="text-xl font-extrabold text-slate-900">{{ $todayMood?->mood ?? 'No mood recorded' }}</p><p class="mt-1 text-sm text-slate-600">{{ $todayMood?->note ?: 'Thanks for checking in with yourself today.' }}</p><a href="{{ route('moods.index') }}" class="mt-4 inline-block text-sm font-extrabold text-purple-700 hover:text-purple-900">See emotion history →</a></div></div>
        </article>
        <article class="student-card overflow-hidden">
            <div class="student-card-header">FocusBuddy</div>
            <div class="flex items-center gap-5 p-6"><div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-3xl bg-blue-100"><img src="{{ asset('images/sps/focusbuddy.png') }}" alt="FocusBuddy penguin" class="h-full w-full object-contain"></div><div><p class="text-xl font-extrabold text-slate-900">Keep your focus going!</p><p class="mt-1 text-sm leading-6 text-slate-600">Complete a task, take a short break, then return ready for the next one.</p><a href="{{ route('penguin') }}" class="mt-4 inline-block text-sm font-extrabold text-purple-700 hover:text-purple-900">Meet your companion →</a></div></div>
        </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.45fr_.85fr]">
        <article class="student-card overflow-hidden">
            <div class="student-card-header flex items-center justify-between"><span>Upcoming Tasks</span><a href="{{ route('tasks.index') }}" class="text-sm text-purple-800">See all</a></div>
            <div class="divide-y divide-slate-100">
                @forelse ($upcomingTasks as $task)
                    <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-extrabold text-slate-900">{{ $task->title }}</p><p class="mt-1 text-sm text-slate-500">Due {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}</p></div><span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-extrabold text-purple-800">Priority score {{ $task->priority_score }}</span></div>
                @empty
                    <div class="p-6 text-sm text-slate-500">No pending tasks yet. Add one to start planning your day.</div>
                @endforelse
            </div>
        </article>
        <article class="student-card overflow-hidden">
            <div class="student-card-header flex items-center justify-between"><span>Notifications</span><span class="rounded-full bg-white px-2.5 py-1 text-xs text-purple-800">{{ $notificationCount }}</span></div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentNotifications as $notification)
                    <div class="p-4"><p class="font-bold text-slate-900">{{ $notification->title }}</p><p class="mt-1 text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($notification->message, 82) }}</p></div>
                @empty
                    <div class="p-6 text-sm text-slate-500">You have no notifications right now.</div>
                @endforelse
            </div>
            <a href="{{ route('notifications.index') }}" class="block border-t border-slate-100 px-5 py-4 text-sm font-extrabold text-purple-700">View notifications →</a>
        </article>
    </section>

    <section class="student-card mt-6 overflow-hidden">
        <div class="student-card-header">Today’s Tasks</div>
        <div class="divide-y divide-slate-100">
            @forelse ($todayTasks as $task)
                <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-extrabold text-slate-900">{{ $task->title }}</p><p class="mt-1 text-sm text-slate-500">{{ $task->priority }} priority · Difficulty {{ $task->difficulty }} · Score {{ $task->priority_score }}</p></div><span class="font-bold {{ $task->status === 'Completed' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $task->status }}</span></div>
            @empty
                <div class="p-6 text-sm text-slate-500">Nothing is due today. Great time to get ahead!</div>
            @endforelse
        </div>
    </section>
@endsection
