@extends('layouts.admin')

@section('content')

<div class="mb-8">
    <p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">
        Student Management
    </p>

    <h1 class="student-page-title mt-1">
        Add Student
    </h1>

    <p class="mt-2 text-slate-600">
        Create a student account for access to the Student Productivity System.
    </p>
</div>

<section class="admin-card overflow-hidden">
    <div class="border-b border-slate-100 p-6">
        <h2 class="text-xl font-extrabold text-slate-900">
            Student Information
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Enter the student's official account information.
        </p>
    </div>

    <form
        method="POST"
        action="{{ route('admin.students.store') }}"
        class="p-6"
        data-loading
    >
        @csrf

        <div class="grid gap-5 md:grid-cols-2">

            {{-- Student ID --}}
            <div>
                <label for="student_id" class="mb-2 block text-sm font-bold text-slate-700">
                    Student ID
                </label>

                <input
                    id="student_id"
                    type="text"
                    name="student_id"
                    value="{{ old('student_id') }}"
                    class="admin-input w-full"
                    placeholder="Example: BKV0425KA001"
                    required
                >

                @error('student_id')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Name --}}
            <div>
                <label for="name" class="mb-2 block text-sm font-bold text-slate-700">
                    Full Name
                </label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="admin-input w-full"
                    placeholder="Student full name"
                    required
                >

                @error('name')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="mb-2 block text-sm font-bold text-slate-700">
                    Email
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="admin-input w-full"
                    placeholder="student@example.com"
                    required
                >

                @error('email')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Course --}}
            <div>
                <label for="course" class="mb-2 block text-sm font-bold text-slate-700">
                    Course
                </label>

                <input
                    id="course"
                    type="text"
                    name="course"
                    value="{{ old('course') }}"
                    class="admin-input w-full"
                    placeholder="Example: KSK"
                    required
                >

                @error('course')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Semester --}}
            <div>
                <label for="semester" class="mb-2 block text-sm font-bold text-slate-700">
                    Semester
                </label>

                <select
                    id="semester"
                    name="semester"
                    class="admin-input w-full"
                    required
                >
                    <option value="">Select semester</option>

                    @for ($semester = 1; $semester <= 8; $semester++)
                        <option
                            value="{{ $semester }}"
                            @selected(old('semester') == $semester)
                        >
                            Semester {{ $semester }}
                        </option>
                    @endfor
                </select>

                @error('semester')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

        </div>

        <div class="my-7 border-t border-slate-100"></div>

        <div>
            <h2 class="text-lg font-extrabold text-slate-900">
                Temporary Password
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                The student will use this password for their initial login.
            </p>
        </div>

        <div class="mt-5 grid gap-5 md:grid-cols-2">

            {{-- Password --}}
            <div>
                <label for="password" class="mb-2 block text-sm font-bold text-slate-700">
                    Password
                </label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    class="admin-input w-full"
                    autocomplete="new-password"
                    required
                >

                @error('password')
                    <p class="mt-2 text-sm font-semibold text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700">
                    Confirm Password
                </label>

                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="admin-input w-full"
                    autocomplete="new-password"
                    required
                >
            </div>

        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <button type="submit" class="admin-button">
                Create Student Account
            </button>

            <a
                href="{{ route('admin.students.index') }}"
                class="admin-button-secondary"
            >
                Cancel
            </a>
        </div>
    </form>
</section>

@endsection