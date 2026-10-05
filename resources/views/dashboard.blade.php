@extends('layouts.student')

@section('content')
    @php
        $moodFaces = ['Happy' => '😊', 'Neutral' => '😐', 'Sad' => '😢', 'Stress' => '😰', 'Angry' => '😠'];
        $moodFace = $moodFaces[$todayMood?->mood] ?? '🙂';
        $firstName = \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ') ?: auth()->user()->name;
        $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening');
        $pendingSummary = $pendingTasks > 0
            ? 'You have '.$pendingTasks.' '.\Illuminate\Support\Str::plural('task', $pendingTasks).' waiting. Let’s make today productive.'
            : 'You’re all caught up. Great work!';
    @endphp

    <div class="mb-6">
        <div>
            <p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Student Productivity System</p>
            <h1 class="student-page-title mt-1">Dashboard</h1>
            <p class="mt-2 text-slate-600">Here’s what’s happening with your productivity today.</p>
        </div>
    </div>

    <section class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <p class="dashboard-hero-date">{{ strtoupper(now()->format('l, j F Y')) }}</p>
            <h2>{{ $greeting }}, {{ $firstName }}</h2>
            <p class="dashboard-hero-summary">{{ $pendingSummary }}</p>
            <a href="{{ route('tasks.index') }}" class="dashboard-hero-button">
                View Tasks <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="dashboard-hero-penguin-wrap">
            <img
                src="{{ $penguinImage }}"
                alt="Level {{ $gamificationLevel['number'] }} {{ $gamificationLevel['name'] }} penguin"
                class="dashboard-hero-penguin"
            >
        </div>
    </section>

    <section class="dashboard-summary-grid mt-5">
        <article class="dashboard-summary-card">
            <span class="dashboard-summary-icon is-pending" aria-hidden="true">◷</span>
            <div><p>Pending Tasks</p><strong>{{ $pendingTasks }}</strong><small>{{ $pendingTasks === 1 ? 'Task waiting for you' : 'Tasks waiting for you' }}</small></div>
        </article>
        <article class="dashboard-summary-card">
            <span class="dashboard-summary-icon is-completed" aria-hidden="true">✓</span>
            <div><p>Completed Tasks</p><strong>{{ $completedTasks }}</strong><small>Tasks completed so far</small></div>
        </article>
        <article class="dashboard-summary-card">
            <span class="dashboard-summary-icon is-upcoming" aria-hidden="true">⌁</span>
            <div><p>Upcoming Deadlines</p><strong>{{ $upcomingTasks->count() }}</strong><small>{{ $upcomingTasks->isEmpty() ? 'No upcoming deadlines' : 'Next scheduled tasks' }}</small></div>
        </article>
        <article class="dashboard-summary-card">
            <span class="dashboard-summary-icon is-mood" aria-hidden="true">{{ $moodFace }}</span>
            <div><p>Today’s Mood</p><strong>{{ $todayMood?->mood ?? 'Not recorded' }}</strong><small>{{ $todayMood ? 'Checked in today' : 'Daily check-in pending' }}</small></div>
        </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <article class="student-card overflow-hidden">
            <div class="student-card-header flex items-center justify-between"><span>Today’s Mood</span><span class="text-2xl">{{ $moodFace }}</span></div>
            <div class="flex items-center gap-4 p-6"><span class="text-6xl">{{ $moodFace }}</span><div><p class="text-xl font-extrabold text-slate-900">{{ $todayMood?->mood ?? 'No mood recorded' }}</p><p class="mt-1 text-sm text-slate-600">{{ $todayMood?->note ?: 'Thanks for checking in with yourself today.' }}</p><a href="{{ route('moods.index') }}" class="mt-4 inline-block text-sm font-extrabold text-purple-700 hover:text-purple-900">See emotion history →</a></div></div>
        </article>
       <article class="student-card overflow-hidden">
    <div class="student-card-header flex items-center justify-between">
        <span>FocusBuddy</span>

        <span class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-purple-800">
            Level {{ $gamificationLevel['number'] }}
        </span>
    </div>

    <div class="grid gap-5 p-6 sm:grid-cols-[150px_1fr] sm:items-center">

        <div class="focusbuddy-stage">
            <img
                src="{{ $penguinImage }}"
                alt="Level {{ $gamificationLevel['number'] }} {{ $gamificationLevel['name'] }} penguin"
                class="focusbuddy-penguin"
            >
        </div>

        <div class="min-w-0">
            <p class="text-xl font-extrabold text-slate-900">
                {{ $gamificationLevel['name'] }}
            </p>

            <p class="mt-1 text-sm leading-6 text-slate-600">
                {{ number_format($gamificationProfile->total_xp) }} XP
                · 🔥 {{ $gamificationProfile->current_streak }}
                {{ $gamificationProfile->current_streak == 1 ? 'day' : 'days' }} streak
            </p>

            <div class="mt-4">
                <div class="mb-2 flex items-center justify-between gap-3 text-xs font-bold text-slate-500">
                    @if ($gamificationNextLevel)
                        <span>
                            Progress to Level {{ $gamificationNextLevel['number'] }}
                        </span>

                        <span>{{ $gamificationPercent }}%</span>
                    @else
                        <span class="font-extrabold text-purple-700">
                            Productivity Master
                        </span>

                        <span class="font-extrabold text-purple-700">
                            MAX LEVEL
                        </span>
                    @endif
                </div>

                <div class="h-2.5 overflow-hidden rounded-full bg-purple-100">
                    <div
                        data-xp-fill="{{ min(100, max(0, $gamificationPercent)) }}%"
                        class="xp-fill h-full w-0 rounded-full bg-purple-700"
                    ></div>
                </div>
            </div>

            <a
                href="{{ route('penguin') }}"
                class="mt-4 inline-block text-sm font-extrabold text-purple-700 hover:text-purple-900"
            >
                View My Penguin →
            </a>
        </div>
    </div>
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
