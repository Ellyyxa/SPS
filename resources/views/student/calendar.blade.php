@extends('layouts.student')
@section('content')
<div class="mb-8"><p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Plan ahead</p><h1 class="student-page-title mt-1">My Calendar</h1></div><div class="student-card p-8 text-center"><div class="mx-auto flex h-24 w-24 items-center justify-center rounded-3xl bg-purple-100 text-5xl">▣</div><h2 class="mt-6 text-2xl font-extrabold text-slate-900">Calendar view is coming soon</h2><p class="mx-auto mt-3 max-w-lg text-slate-600">Your task due dates are already saved in SPS. This visual calendar will be connected in a future phase.</p><a href="{{ route('tasks.index') }}" class="student-button mt-6">View tasks</a></div>
@endsection
