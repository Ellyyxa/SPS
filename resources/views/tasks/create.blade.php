@extends('layouts.student')
@section('content')
<div class="mx-auto max-w-3xl"><div class="mb-8 flex items-end justify-between"><div><p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Task planner</p><h1 class="student-page-title mt-1">New Task</h1></div><a href="{{ route('tasks.index') }}" class="student-button-secondary">Back to tasks</a></div><form method="POST" action="{{ route('tasks.store') }}" class="student-card p-6 sm:p-8">@csrf @include('tasks.partials.form', ['task' => null, 'submit' => 'Save Task'])</form></div>
@endsection
