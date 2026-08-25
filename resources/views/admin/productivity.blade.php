<h1>Productivity Report</h1>

<hr>

@foreach ($students as $student)

    <h3>{{ $student->name }}</h3>

    <p>
        Total Tasks:
        {{ $student->tasks_count }}
    </p>

    <p>
        Completed Tasks:
        {{ $student->completed_tasks }}
    </p>

    <p>
        Pending Tasks:
        {{ $student->pending_tasks }}
    </p>

    <p>
        Average Priority Score:
        {{ number_format($student->tasks_avg_priority_score ?? 0, 2) }}
    </p>

    <hr>

@endforeach