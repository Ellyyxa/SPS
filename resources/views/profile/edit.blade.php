@extends('layouts.student')

@section('content')

<div class="mb-8">
    <p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">
        Your Account
    </p>

    <h1 class="student-page-title mt-1">
        My Profile
    </h1>
</div>


<div class="grid gap-6 xl:grid-cols-[.75fr_1.25fr]">

    {{-- LEFT: Student Information --}}
    <aside class="student-card p-7">

        <div class="profile-avatar h-24 w-24 text-3xl">

            @if ($user->profile_photo_path)

                <img
                    src="{{ asset('storage/' . $user->profile_photo_path) }}"
                    alt="{{ $user->name }}'s profile photo"
                >

            @else

                {{ strtoupper(mb_substr($user->name, 0, 1)) }}

            @endif

        </div>


        <h2 class="mt-5 text-xl font-extrabold">
            {{ $user->name }}
        </h2>

        <p class="mt-1 text-sm text-slate-600">
            {{ $user->student_id ?: 'Student' }}
        </p>


        <dl class="mt-6 space-y-3 text-sm">

            <div>
                <dt class="font-bold text-slate-500">Email</dt>
                <dd class="break-words">{{ $user->email }}</dd>
            </div>

            <div>
                <dt class="font-bold text-slate-500">Programme</dt>
                <dd>{{ $user->programme ?: 'Not provided' }}</dd>
            </div>

            <div>
                <dt class="font-bold text-slate-500">Course</dt>
                <dd>{{ $user->course ?: 'Not provided' }}</dd>
            </div>

            <div>
                <dt class="font-bold text-slate-500">Semester</dt>
                <dd>{{ $user->semester ?: 'Not provided' }}</dd>
            </div>

        </dl>

    </aside>


    {{-- RIGHT --}}
    <div class="space-y-6">

        {{-- Profile Photo + Read-only Information --}}
        <div class="student-card p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>


        {{-- Password --}}
        <div class="student-card p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

    </div>

</div>

@endsection