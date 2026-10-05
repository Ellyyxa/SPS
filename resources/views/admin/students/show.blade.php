@extends('layouts.admin')

@section('content')

@php
    $rate = $user->tasks_count
        ? round($user->completed_tasks / $user->tasks_count * 100)
        : 0;
@endphp

<div class="flex items-center justify-between gap-4">

    <a
        class="text-sm font-bold text-purple-800"
        href="{{ route('admin.students.index') }}"
    >
        ← Student Accounts
    </a>

    <a
        href="{{ route('admin.students.edit', $user) }}"
        class="admin-button"
    >
        Edit Student
    </a>

</div>


<div class="mt-4 grid gap-6 xl:grid-cols-2">

    <section class="admin-card p-6">

        <h1 class="text-2xl font-extrabold">
            {{ $user->name }}
        </h1>

        <p class="text-slate-500">
            {{ $user->student_id }}
        </p>

        <dl class="mt-5 space-y-3">

            <div>
                <dt class="font-bold text-slate-500">
                    Email
                </dt>

                <dd>
                    {{ $user->email }}
                </dd>
            </div>

            <div>
    <dt class="font-bold text-slate-500">
        Programme
    </dt>

    <dd>
        {{ $user->programme ?? '-' }}
    </dd>
</div>

<div>
    <dt class="font-bold text-slate-500">
        Course / Semester
    </dt>

    <dd>
        {{ $user->course }} · Semester {{ $user->semester }}
    </dd>
</div>

        </dl>

    </section>


    <section class="grid gap-4 sm:grid-cols-4">

        <div class="admin-stat">
            <p>Tasks</p>
            <p>{{ $user->tasks_count }}</p>
        </div>

        <div class="admin-stat">
            <p>Completed</p>
            <p>{{ $user->completed_tasks }}</p>
        </div>

        <div class="admin-stat">
            <p>Pending</p>
            <p>{{ $user->pending_tasks }}</p>
        </div>

        <div class="admin-stat">
            <p>Rate</p>
            <p>{{ $rate }}%</p>
        </div>

    </section>

</div>


<section class="admin-card mt-6 overflow-hidden">

    <div class="border-b p-4 font-extrabold">
        Recent emotion history
    </div>

    @forelse($moods as $mood)

        <div class="flex justify-between border-b p-4">

            <span>
                <strong>{{ $mood->mood }}</strong>

                <small class="block">
                    {{ $mood->note ?: 'No note' }}
                </small>
            </span>

            <small>
                {{ $mood->date->format('d M Y') }}
            </small>

        </div>

    @empty

        <p class="p-5">
            No mood records.
        </p>

    @endforelse

</section>

@endsection