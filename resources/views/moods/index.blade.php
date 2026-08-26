@extends('layouts.student')

@section('content')
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Your wellbeing</p><h1 class="student-page-title mt-1">My Emotion History</h1></div><a class="student-button" href="{{ route('moods.create') }}">Daily check-in</a></div>
    <div class="student-card overflow-hidden"><div class="student-card-header">Recent emotions</div><div class="divide-y divide-slate-100">@forelse($moods as $mood)<div class="flex flex-col gap-2 p-5 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-extrabold text-slate-900">{{ $mood->mood }} <span class="ml-1">{{ ['Happy'=>'😊','Neutral'=>'😐','Sad'=>'😢','Stress'=>'😰','Angry'=>'😠'][$mood->mood] ?? '🙂' }}</span></p><p class="mt-1 text-sm text-slate-600">{{ $mood->note ?: 'No note added.' }}</p></div><span class="text-sm font-bold text-slate-500">{{ \Carbon\Carbon::parse($mood->date)->format('d M Y') }}</span></div>@empty<div class="p-6 text-slate-500">No emotion history yet.</div>@endforelse</div></div>
@endsection
