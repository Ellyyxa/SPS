@extends('layouts.mood-checkin')

@section('content')
    @php
        $selectedDate = old('date', $checkInDates->first()['value']);
        $selectedDate = is_string($selectedDate) ? $selectedDate : '';
        $selectedOption = $checkInDates->firstWhere('value', $selectedDate);
        $alreadyRecorded = $selectedOption['recorded'] ?? false;
        $cannotSubmit = ! $selectedOption || $alreadyRecorded;
    @endphp
    <div class="mx-auto max-w-4xl">
        <div class="mb-8 text-center"><p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Your wellbeing</p><h1 class="student-page-title mt-2">Evening Emotion Check-in</h1><p class="mt-2 text-slate-600">Take a moment to reflect on your day.</p><p class="mt-2 text-sm text-slate-500">Check in whenever it suits you. This is always optional.</p></div>
        <form method="POST" action="{{ route('moods.store') }}" class="student-card p-6 sm:p-10" data-checkin-form>@csrf
            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-xl bg-rose-50 p-4 text-sm text-rose-700">
                    <p class="font-bold">Please review your check-in.</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                </div>
            @endif
            <div class="mb-6">
                <label for="checkin-date" class="text-sm font-extrabold text-slate-800">Which day are you reflecting on?</label>
                <select id="checkin-date" name="date" data-checkin-date required aria-describedby="checkin-date-help checkin-date-status" @error('date') aria-invalid="true" @enderror class="mt-2 block w-full rounded-xl border-slate-300 bg-slate-50 text-slate-900 focus:border-purple-600 focus:ring-purple-600">
                    @if (! $selectedOption)
                        <option value="{{ $selectedDate }}" selected data-unavailable="true">Previously selected: {{ $selectedDate ?: 'Invalid date' }} — no longer eligible</option>
                    @endif
                    @foreach ($checkInDates as $option)
                        <option value="{{ $option['value'] }}" data-recorded="{{ $option['recorded'] ? 'true' : 'false' }}" @selected($selectedDate === $option['value'])>{{ $option['label'] }} — {{ $option['display'] }}{{ $option['recorded'] ? ' — Already checked in' : '' }}</option>
                    @endforeach
                </select>
                <p id="checkin-date-help" class="mt-2 text-xs text-slate-500">Dates use Malaysia time. You can record today or yesterday, once per day.</p>
                <p id="checkin-date-status" data-checkin-status role="status" aria-live="polite" class="mt-2 text-sm font-semibold text-purple-800">{{ $alreadyRecorded ? 'Already checked in for this date. Choose another available day.' : (! $selectedOption ? 'This date is no longer eligible. Please choose Today or Yesterday.' : 'This date is available for check-in.') }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                @foreach (['Happy' => '😊', 'Neutral' => '😐', 'Sad' => '😢', 'Stress' => '😰', 'Angry' => '😠'] as $value => $face)
                    <label class="cursor-pointer"><input data-mood-option type="radio" name="mood" value="{{ $value }}" class="peer sr-only" {{ old('mood') === $value ? 'checked' : '' }}><span class="flex h-full flex-col items-center rounded-2xl border-2 border-slate-200 bg-white p-4 text-center transition hover:-translate-y-1 hover:border-purple-400 peer-checked:border-purple-700 peer-checked:bg-purple-50 peer-focus:ring-2 peer-focus:ring-purple-400"><span class="text-5xl">{{ $face }}</span><span class="mt-3 text-sm font-extrabold text-slate-800">{{ $value }}</span></span></label>
                @endforeach
            </div>
            @error('mood')<p class="mt-4 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
            <div class="mt-8"><label for="note" class="text-sm font-extrabold text-slate-800">A note for this day <span class="font-medium text-slate-400">(optional)</span></label><textarea id="note" name="note" rows="4" maxlength="2000" class="mt-2 block w-full rounded-xl border-slate-300 bg-slate-50 text-slate-900 shadow-sm focus:border-purple-600 focus:ring-purple-600" placeholder="What would you like to reflect on?">{{ is_string(old('note')) ? old('note') : '' }}</textarea>@error('note')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror</div>
            <div class="mt-8 text-center">
                <div class="flex flex-wrap justify-center gap-3">
                    <button type="submit" data-checkin-save @disabled($cannotSubmit) class="student-button min-w-52 disabled:cursor-not-allowed disabled:opacity-50">Save mood</button>
                    <a href="{{ route('dashboard') }}" class="student-button-secondary">Not Now</a>
                </div>
                <p class="mt-3 text-xs font-semibold text-slate-500">Check-in is optional. You can save one mood each day.</p>
            </div>
        </form>
    </div>
@endsection
