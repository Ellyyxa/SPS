@extends('layouts.admin')
@section('content')
<div class="mb-6">
    <p class="text-sm font-bold uppercase tracking-[.18em] text-purple-700">Student wellbeing</p>
    <h1 class="student-page-title mt-1">Emotion Overview</h1>
    <p class="mt-2 text-sm text-slate-600">Cohort: current student accounts, not verified historical enrollment. Historical participation rates cannot be inferred.</p>
    <p class="mt-2 font-bold text-purple-800">{{ $start->format('d M Y') }} – {{ $end->format('d M Y') }} · Malaysia calendar dates</p>
    <p class="mt-1 text-sm text-slate-600">As of {{ $asOf->format('d M Y, h:i A') }} (Asia/Kuala_Lumpur). Late entries may change these counts.</p>
</div>
<form method="GET" action="{{ route('admin.emotions.index') }}" class="admin-card mb-6 grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4" data-emotion-period-form>
    <label class="text-sm font-bold">Analysis period<select class="admin-input mt-2" name="period" data-emotion-period>@foreach(['date' => 'Exact date', '7' => 'Last 7 Days', '30' => 'Last 30 Days', 'year' => 'This Year', 'custom' => 'Custom Date Range'] as $value => $label)<option value="{{ $value }}" @selected($period === (string) $value)>{{ $label }}</option>@endforeach</select></label>
    <label class="text-sm font-bold">Exact date<input class="admin-input mt-2" type="date" name="date" value="{{ $selectedDate }}" max="{{ today('Asia/Kuala_Lumpur')->toDateString() }}" data-emotion-date></label>
    <label class="text-sm font-bold">Custom start<input class="admin-input mt-2" type="date" name="start_date" value="{{ $start->toDateString() }}" max="{{ today('Asia/Kuala_Lumpur')->toDateString() }}" data-emotion-custom></label>
    <label class="text-sm font-bold">Custom end<input class="admin-input mt-2" type="date" name="end_date" value="{{ $end->toDateString() }}" max="{{ today('Asia/Kuala_Lumpur')->toDateString() }}" data-emotion-custom><span class="block mt-1 text-xs font-normal">Maximum five years per request.</span></label>
    <label class="text-sm font-bold">Programme<select class="admin-input mt-2" name="programme"><option value="">All programmes</option>@foreach(['SVM', 'DVM'] as $value)<option value="{{ $value }}" @selected(request('programme') === $value)>{{ $value }}</option>@endforeach</select></label>
    <label class="text-sm font-bold">Course<select class="admin-input mt-2" name="course"><option value="">All courses</option>@foreach($courses as $value)<option value="{{ $value }}" @selected(request('course') === $value)>{{ $value }}</option>@endforeach</select></label>
    <label class="text-sm font-bold">Semester<select class="admin-input mt-2" name="semester"><option value="">All semesters</option>@foreach($semesters as $value)<option value="{{ $value }}" @selected((string) request('semester') === (string) $value)>{{ $value }}</option>@endforeach</select></label>
    <div class="flex flex-wrap items-end gap-2"><button class="admin-button" type="submit">Apply filters</button><a class="admin-button-secondary" href="{{ route('admin.emotions.index') }}">Reset</a></div>
    @if($errors->any())<div class="sm:col-span-2 text-sm text-red-700" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
</form>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach(['total' => 'Current student accounts', 'participants' => 'Unique students in selected period', 'missing' => 'No check-in in selected period'] + ($period === 'date' ? ['percentage' => 'Participation (current accounts)'] : []) as $key => $label)
        <section class="admin-stat"><h2 class="text-sm font-bold text-slate-600">{{ $label }}</h2><p>{{ $overview[$key] }}{{ $key === 'percentage' ? '%' : '' }}</p></section>
    @endforeach
    @if($period !== 'date')<section class="admin-stat"><h2 class="text-sm font-bold text-slate-600">Total check-in entries in period</h2><p>{{ $entries }}</p></section>@endif
</div>
<p class="my-4 text-sm text-slate-600">Check-ins are optional. No check-in recorded is not an emotion category or evidence of emotional distress.</p>
<section class="admin-card p-5">
    <h2 class="font-extrabold">Mood category entries — selected period</h2>
    <p class="mt-1 text-sm text-slate-600">One entry per student per represented date. Categories are not diagnoses or wellbeing scores.</p>
    <div class="grid gap-5 md:grid-cols-2">
        <canvas class="admin-chart" data-admin-chart="#admin-emotions-data" role="img" aria-label="Mood category distribution" aria-describedby="emotion-category-summary"></canvas>
        <div id="emotion-category-summary" class="self-center">
            @foreach($distribution as $category => $count)<p class="flex justify-between gap-4 border-b border-purple-100 py-2"><span>{{ $category }}</span><strong>{{ $count }}</strong></p>@endforeach
            @if(!$overview['total'])<p class="mt-3 text-sm text-slate-600">No current student accounts match this cohort.</p>@elseif(!$overview['participants'])<p class="mt-3 text-sm text-slate-600">No check-ins recorded for this date and cohort.</p>@endif
        </div>
    </div>
</section>
@if($period !== 'date')
<section class="admin-card mt-6 min-w-0 p-5">
    <h2 class="font-extrabold">{{ $analytics['mode'] === 'monthly' ? 'Monthly' : 'Daily' }} mood-category counts</h2>
    <p class="mt-1 text-sm text-slate-600">{{ $start->format('d M Y') }} – {{ $end->format('d M Y') }}. Blank buckets mean no check-ins recorded, not an invented emotion. Partial boundary months include only selected dates.</p>
    <div class="overflow-x-auto"><canvas class="admin-chart" data-admin-emotion-series="categories" role="img" aria-label="Mood category entry counts by calendar period" aria-describedby="admin-trend-summary"></canvas></div>
    <div class="mt-4 flex flex-wrap gap-4 text-sm">@foreach(['Happy' => '#7c3aed', 'Neutral' => '#3b82f6', 'Sad' => '#eab308', 'Stress' => '#ea580c', 'Angry' => '#dc2626'] as $category => $color)<span><span style="color: {{ $color }}" aria-hidden="true">●</span> {{ $category }}</span>@endforeach</div>
    <h2 class="mt-6 font-extrabold">{{ $analytics['mode'] === 'monthly' ? 'Monthly entries and unique participating students' : 'Unique daily student check-ins' }}</h2>
    <p class="mt-1 text-sm text-slate-600">Counts, not historical enrollment percentages. Monthly unique students are counted once within each month and must not be summed across months.</p>
    <div class="overflow-x-auto"><canvas class="admin-chart" data-admin-emotion-series="participation" role="img" aria-label="Check-in participation counts by calendar period" aria-describedby="admin-trend-summary"></canvas></div>
    <p class="mt-2 text-sm text-slate-600">Purple: unique participating students. @if($analytics['mode'] === 'monthly')Blue: total check-in entries.@endif</p>
    @if(!$entries)<p class="my-3 text-sm text-slate-600">No check-ins recorded in this period and cohort.</p>@endif
    <details class="mt-5" id="admin-trend-summary"><summary class="cursor-pointer font-bold text-purple-800">Accessible chart data — counts by calendar period</summary>
        <div class="mt-3 max-h-96 overflow-auto" tabindex="0" aria-label="Scrollable trend data"><table class="admin-table"><caption class="p-3 text-left">Selected cohort and date range; zero entries means no check-in recorded.</caption><thead><tr><th scope="col">Period</th>@foreach($analytics['categories'] as $category)<th scope="col">{{ $category }}</th>@endforeach<th scope="col">Entries</th><th scope="col">Unique students</th></tr></thead><tbody>
        @foreach($analytics['series'] as $bucket)<tr><th scope="row" class="px-4 text-left text-sm">{{ $bucket['label'] }}</th>@foreach($bucket['counts'] as $count)<td>{{ $count }}</td>@endforeach<td>{{ $bucket['entries'] }}</td><td>{{ $bucket['participants'] }}</td></tr>@endforeach
        </tbody></table></div>
    </details>
</section>
<script id="admin-emotion-series-data" type="application/json">@json($analytics)</script>
@endif
<section class="admin-card mt-6 overflow-hidden">
    <h2 class="p-5 font-extrabold">Check-in records for selected period and cohort</h2>
    <div class="overflow-x-auto"><table class="admin-table"><thead><tr><th scope="col">Student</th><th scope="col">Mood</th><th scope="col">Date</th><th scope="col">History</th></tr></thead><tbody>
        @forelse($moods as $entry)<tr><td>{{ $entry->user->name }}<small class="block">{{ $entry->user->student_id }}</small></td><td><span class="admin-badge admin-badge-mood">{{ $entry->mood }}</span></td><td>{{ $entry->date->format('d M Y') }}</td><td><a class="admin-button-secondary relative" href="{{ route('admin.emotions.show', $entry->user) }}">History<span class="sr-only"> for {{ $entry->user->name }}</span></a></td></tr>
        @empty<tr><td colspan="4" class="text-center">No mood records match.</td></tr>@endforelse
    </tbody></table></div>
    <div class="p-5">{{ $moods->links() }}</div>
</section>
<script id="admin-emotions-data" type="application/json">@json($distribution)</script>
@endsection
