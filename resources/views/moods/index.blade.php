@extends('layouts.student')
@section('content')
@php($faces=['Happy'=>'😊','Neutral'=>'😐','Sad'=>'😢','Stress'=>'😰','Angry'=>'😠'])
<div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div><p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Your wellbeing</p><h1 class="student-page-title mt-1">My Emotion History</h1></div>
    @if (auth()->user()->role === 'student' && ! $hasTodayMood)
        <a href="{{ route('moods.create') }}" class="student-button">Check in today</a>
    @endif
</div>
<form method="GET" action="{{ route('moods.index') }}" class="student-card mb-6 p-5 sm:p-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:items-end">
        <div><label for="history-period" class="block text-sm font-bold text-slate-700">Period</label><select id="history-period" name="period" class="mt-2 block w-full rounded-xl border-slate-300 text-sm">
            @foreach (['7' => 'Last 7 Days', '30' => 'Last 30 Days', 'year' => 'This Year', 'custom' => 'Custom Date Range'] as $value => $label)
                <option value="{{ $value }}" @selected(old('period', $period) == $value)>{{ $label }}</option>
            @endforeach
        </select></div>
        <div><label for="history-start" class="block text-sm font-bold text-slate-700">Start date (custom)</label><input id="history-start" name="start_date" type="date" value="{{ old('start_date', $start->toDateString()) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
        <div><label for="history-end" class="block text-sm font-bold text-slate-700">End date (custom)</label><input id="history-end" name="end_date" type="date" max="{{ now('Asia/Kuala_Lumpur')->toDateString() }}" value="{{ old('end_date', $end->toDateString()) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm"></div>
        <button type="submit" class="student-button">Apply filters</button>
    </div>
    <p class="mt-3 text-xs text-slate-500">Dates use Malaysia time. Custom ranges can cover up to five years. Start/end dates apply only to Custom Date Range.</p>
    @if ($errors->any())<div role="alert" class="mt-3 text-sm text-rose-700">@foreach ($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
</form>
<p class="mb-4 text-sm font-semibold text-slate-600">{{ $start->format('d M Y') }} – {{ $end->format('d M Y') }} · {{ $moods->total() }} {{ \Illuminate\Support\Str::plural('check-in', $moods->total()) }}</p>
<div class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
    <section class="student-card min-w-0 p-5 sm:p-6">
        <h2 class="mb-4 text-lg font-extrabold text-slate-900">{{ $historyChart['mode'] === 'monthly' ? 'Monthly mood distribution' : 'Mood trend' }}</h2>
        <div class="h-72"><canvas class="mood-chart" data-mood-chart aria-label="{{ $historyChart['mode'] === 'monthly' ? 'Monthly check-in counts by mood category' : 'Mood categories by calendar date, with gaps for missing check-ins' }}">Your recorded moods are also available in the history list.</canvas></div>
        @if ($historyChart['mode'] === 'monthly')
            <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-xs text-slate-600" aria-label="Chart categories">@foreach ($faces as $label => $face)<li class="flex items-center gap-1"><span aria-hidden="true" class="inline-block h-2 w-2 rounded-full" style="background-color: {{ $historyChart['colors'][$label] }}"></span>{{ $face }} {{ $label }}</li>@endforeach</ul>
        @endif
        <p class="mt-3 text-xs text-slate-500">{{ $historyChart['mode'] === 'monthly' ? 'Stacked bars show the number of recorded check-ins in each mood category. Longer custom ranges also use monthly counts.' : 'Every calendar day is shown. Missing check-ins leave gaps; categories are not a numerical wellbeing score.' }}</p>
        @if (! $moods->total())<p class="mt-4 text-sm text-slate-500">No mood records in this period. Try another date range.</p>@endif
    </section>
    <section class="student-card min-w-0 overflow-hidden">
        <div class="student-card-header">Recent emotions</div>
        <div class="divide-y divide-slate-100">
            @forelse($moods as $mood)
                <article class="p-4"><div class="flex flex-wrap items-center justify-between gap-2"><h2 class="font-extrabold text-slate-900">{{ $faces[$mood->mood] ?? '🙂' }} {{ $mood->mood }}</h2><time datetime="{{ $mood->date->toDateString() }}" class="text-xs font-bold text-slate-500">{{ $mood->date->format('d M Y') }}</time></div><p class="mt-2 whitespace-pre-line break-words text-sm text-slate-600">{{ $mood->note ?: 'No note added.' }}</p></article>
            @empty
                <div class="p-6 text-sm text-slate-500">No emotion history in this period.</div>
            @endforelse
        </div>
        <div class="border-t border-slate-100 p-4">{{ $moods->links() }}</div>
    </section>
</div>
<script id="mood-history" type="application/json">@json($historyChart)</script>
@endsection
