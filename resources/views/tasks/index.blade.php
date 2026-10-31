@extends('layouts.student')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Plan your workload</p><h1 class="student-page-title mt-1">My Tasks</h1><p class="mt-2 text-sm text-slate-600">Small steps today. A clearer plan for tomorrow.</p></div>
        <a href="{{ route('tasks.create') }}" class="student-button">+ Add Task</a>
    </div>

    <section class="task-summary" aria-label="Task summary">
        <div><span>Total tasks</span><strong>{{ $tasks->count() }}</strong></div>
        <div><span>Pending</span><strong>{{ $tasks->where('status', 'Pending')->count() }}</strong></div>
        <div><span>Completed</span><strong>{{ $tasks->where('status', 'Completed')->count() }}</strong></div>
    </section>

    <section data-task-workspace aria-label="Your tasks">
        <div class="student-card task-controls">
            <div class="task-toolbar">
                <div><label for="task-search">Search tasks</label><input id="task-search" data-task-filter type="search" placeholder="Search title or description" aria-controls="task-list"></div>
                <div><label for="task-priority">Priority</label><select id="task-priority" data-task-priority aria-controls="task-list"><option value="">All priorities</option><option>High</option><option>Medium</option><option>Low</option></select></div>
                <div><label for="task-sort">Sort by</label><select id="task-sort" data-task-sort aria-controls="task-list"><option value="priority">Priority score: highest first</option><option value="due">Due date: earliest first</option><option value="due-desc">Due date: latest first</option></select></div>
            </div>
            <fieldset class="task-status-tabs">
                <legend class="sr-only">Filter by status</legend>
                @foreach (['' => 'All', 'Pending' => 'Pending', 'Completed' => 'Completed'] as $value => $label)
                    <label><input type="radio" name="task_status" data-task-status value="{{ $value }}" @checked($value === '') aria-controls="task-list"><span>{{ $label }}</span></label>
                @endforeach
            </fieldset>
        </div>

        <p class="task-result-count" data-task-result role="status" aria-live="polite">{{ $tasks->count() }} {{ \Illuminate\Support\Str::plural('task', $tasks->count()) }}</p>
        <div id="task-list" class="task-card-grid" data-task-list>
            @foreach ($tasks as $task)
                <article class="task-card student-card" data-task-row
                    data-task-status-value="{{ $task->status }}" data-task-priority-value="{{ $task->priority }}"
                    data-task-search="{{ $task->title.' '.$task->description }}"
                    data-task-score="{{ $task->priority_score }}" data-task-due="{{ \Carbon\Carbon::parse($task->due_date)->timestamp }}">
                    <div class="task-card-heading">
                        <h2>{{ $task->title }}</h2>
                        <span class="task-badge {{ $task->status === 'Completed' ? 'is-completed' : 'is-pending' }}">{{ $task->status }}</span>
                    </div>
                    <p class="task-description">{{ $task->description ?: 'No description added.' }}</p>
                    <dl class="task-metadata">
                        <div><dt>Due date</dt><dd><time datetime="{{ \Carbon\Carbon::parse($task->due_date)->toDateString() }}">{{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}</time></dd></div>
                        <div><dt>Priority</dt><dd><span class="task-badge priority-{{ strtolower($task->priority ?? 'low') }}">{{ $task->priority ?? 'Not set' }}</span></dd></div>
                        <div><dt>Difficulty</dt><dd>{{ [1 => 'Easy', 2 => 'Medium', 3 => 'Hard'][$task->difficulty] ?? $task->difficulty }}</dd></div>
                        <div><dt>Smart Priority Score</dt><dd>{{ $task->priority_score }}</dd></div>
                    </dl>
                    <div class="task-card-actions">
                        <a href="{{ route('tasks.edit', $task) }}" class="student-button-secondary">Edit</a>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" data-confirm="Delete ‘{{ $task->title }}’? This cannot be undone.">
                            @csrf @method('DELETE')
                            <button class="task-delete" type="submit">Delete</button>
                        </form>
                        @if ($task->status === 'Pending')
                            <form action="{{ route('tasks.complete', $task) }}" method="POST" data-loading>
                                @csrf @method('PATCH')
                                <button class="student-button" type="submit">Mark Complete</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="student-card task-empty" data-task-empty @if ($tasks->isNotEmpty()) hidden @endif>
            <h2>{{ $tasks->isEmpty() ? 'Your next step starts here' : 'No tasks match your filters' }}</h2>
            <p>{{ $tasks->isEmpty() ? 'Add your first task and start planning your day.' : 'Try another search, status, or priority.' }}</p>
            @if ($tasks->isEmpty())
                <a href="{{ route('tasks.create') }}" class="student-button">+ Add Task</a>
            @else
                <button type="button" class="student-button-secondary" data-task-reset>Clear filters</button>
            @endif
        </div>
    </section>
@endsection
