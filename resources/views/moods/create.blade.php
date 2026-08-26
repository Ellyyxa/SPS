@extends('layouts.student')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-8 text-center"><p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Daily check-in</p><h1 class="student-page-title mt-2">How are you feeling today?</h1><p class="mt-2 text-slate-600">Choose the emotion that feels closest to your day.</p></div>
        <form method="POST" action="{{ route('moods.store') }}" class="student-card p-6 sm:p-10">@csrf
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                @foreach (['Happy' => '😊', 'Neutral' => '😐', 'Sad' => '😢', 'Stress' => '😰', 'Angry' => '😠'] as $value => $face)
                    <label class="cursor-pointer"><input data-mood-option type="radio" name="mood" value="{{ $value }}" class="peer sr-only" {{ old('mood') === $value ? 'checked' : '' }}><span class="flex h-full flex-col items-center rounded-2xl border-2 border-slate-200 bg-white p-4 text-center transition hover:-translate-y-1 hover:border-purple-400 peer-checked:border-purple-700 peer-checked:bg-purple-50 peer-focus:ring-2 peer-focus:ring-purple-400"><span class="text-5xl">{{ $face }}</span><span class="mt-3 text-sm font-extrabold text-slate-800">{{ $value }}</span></span></label>
                @endforeach
            </div>
            @error('mood')<p class="mt-4 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
            <div class="mt-8"><label for="note" class="text-sm font-extrabold text-slate-800">A note for today <span class="font-medium text-slate-400">(optional)</span></label><textarea id="note" name="note" rows="4" class="mt-2 block w-full rounded-xl border-slate-300 bg-slate-50 text-slate-900 shadow-sm focus:border-purple-600 focus:ring-purple-600" placeholder="How are you feeling today?">{{ old('note') }}</textarea>@error('note')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror</div>
            <div class="mt-8 text-center"><button type="submit" class="student-button min-w-52">Save today’s mood</button><p class="mt-3 text-xs font-semibold text-slate-500">You can check in once each day.</p></div>
        </form>
    </div>
@endsection
