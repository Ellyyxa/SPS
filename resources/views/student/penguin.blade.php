@extends('layouts.student')

@section('content')
@php
    $currentLevelXp = $level['xp'];
    $xpIntoLevel = max(0, $profile->total_xp - $currentLevelXp);
    $xpNeededForLevel = $nextLevel
        ? $nextLevel['xp'] - $currentLevelXp
        : 0;

    $achievementIcons = [
        'first_step' => '🏅',
        'getting_things_done' => '⭐',
        'consistency_star' => '🔥',
        'productivity_master' => '🏆',
    ];
@endphp

<div class="mb-8">
    <p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">
        Your companion
    </p>

    <h1 class="student-page-title mt-1">My Penguin</h1>
</div>

<div class="student-card grid gap-8 p-8 md:grid-cols-2 md:items-center">

    {{-- Penguin --}}
    <div class="flex min-h-72 items-center justify-center rounded-3xl bg-blue-100 p-6">
        <<img
    src="{{ $penguinImage }}"
    alt="Level {{ $level['number'] }} {{ $level['name'] }} penguin"
    class="my-penguin-character"
>
    </div>

    {{-- Gamification information --}}
    <div>

        <span class="rounded-full bg-purple-100 px-3 py-1 text-sm font-extrabold text-purple-800">
            Level {{ $level['number'] }} · {{ $level['name'] }}
        </span>

        <h2 class="mt-4 text-3xl font-extrabold text-slate-900">
            Your productivity companion
        </h2>

        <p class="mt-3 leading-7 text-slate-600">
            Complete tasks and check in with your wellbeing to earn XP,
            build your streak and evolve your penguin.
        </p>

        {{-- XP --}}
        <div class="mt-6">

            <div class="flex items-center justify-between gap-4 text-sm font-bold text-slate-700">
                <span>{{ number_format($profile->total_xp) }} Total XP</span>

                @if ($nextLevel)
                    <span>
                        {{ number_format($xpIntoLevel) }}
                        /
                        {{ number_format($xpNeededForLevel) }} XP
                    </span>
                @else
                    <span class="text-purple-700">MAX LEVEL</span>
                @endif
            </div>

            <div class="mt-2 h-3 overflow-hidden rounded-full bg-purple-100">
                <div
                    class="xp-fill h-full rounded-full bg-purple-700 transition-all duration-700"
                    style="width: {{ min(100, max(0, $progressPercent)) }}%;"
                ></div>
            </div>

            @if ($nextLevel)
                <p class="mt-2 text-xs font-semibold text-slate-500">
                    Next: Level {{ $nextLevel['number'] }} · {{ $nextLevel['name'] }}
                </p>
            @else
                <p class="mt-2 text-xs font-extrabold text-purple-700">
                    Productivity Master achieved!
                </p>
            @endif

        </div>

        {{-- Streak --}}
        <div class="mt-6 grid grid-cols-2 gap-3">

            <div class="rounded-2xl bg-orange-50 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                    Current Streak
                </p>

                <p class="mt-1 text-2xl font-extrabold text-slate-900">
                    🔥 {{ $profile->current_streak }} days
                </p>
            </div>

            <div class="rounded-2xl bg-purple-50 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                    Longest Streak
                </p>

                <p class="mt-1 text-2xl font-extrabold text-slate-900">
                    ⭐ {{ $profile->longest_streak }} days
                </p>
            </div>

        </div>

        <a href="{{ route('dashboard') }}" class="student-button mt-6">
            Back to dashboard
        </a>

    </div>
</div>

{{-- Achievements --}}
<div class="student-card mt-6 p-8">

    <div>
        <p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">
            Milestones
        </p>

        <h2 class="mt-1 text-2xl font-extrabold text-slate-900">
            Achievements
        </h2>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ($achievements as $achievement)

            <div
                class="rounded-3xl border p-5
                {{ $achievement['unlocked']
                    ? 'border-purple-200 bg-purple-50'
                    : 'border-slate-200 bg-slate-50 opacity-70' }}"
            >

                <div class="text-3xl">
                    {{ $achievementIcons[$achievement['key']] ?? '🏅' }}
                </div>

                <p class="mt-3 font-extrabold text-slate-900">
                    {{ $achievement['name'] }}
                </p>

                <p class="mt-1 min-h-10 text-sm leading-5 text-slate-600">
                    {{ $achievement['description'] }}
                </p>

                @if ($achievement['unlocked'])

                    <span class="mt-4 inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-extrabold text-emerald-700">
                        ✓ Unlocked
                    </span>

                    @if ($achievement['unlocked_at'])
                        <p class="mt-2 text-xs text-slate-500">
                            {{ \Carbon\Carbon::parse($achievement['unlocked_at'])->format('d M Y') }}
                        </p>
                    @endif

                @else

                    <span class="mt-4 inline-block rounded-full bg-slate-200 px-3 py-1 text-xs font-extrabold text-slate-600">
                        🔒 Locked
                    </span>

                @endif

            </div>

        @endforeach

    </div>
</div>

@endsection