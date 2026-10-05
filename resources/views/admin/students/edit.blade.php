@extends('layouts.admin')

@section('content')

<div class="mb-8">

    <a
        href="{{ route('admin.students.show', $user) }}"
        class="text-sm font-bold text-purple-800"
    >
        ← Student Details
    </a>

    <h1 class="student-page-title mt-4">
        Edit Student
    </h1>

    <p class="mt-2 text-sm text-slate-500">
        Update the student's account and academic information.
    </p>

</div>


<div class="admin-card max-w-3xl p-6">

    @if($errors->any())

        <div class="mb-6 rounded-xl bg-red-50 p-4 text-sm text-red-700">

            <p class="font-bold">
                Please check the information below.
            </p>

            <ul class="mt-2 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif


    <form
        method="POST"
        action="{{ route('admin.students.update', $user) }}"
        class="space-y-5"
    >

        @csrf
        @method('PATCH')


        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">
                Student ID
            </label>

            <input
                type="text"
                name="student_id"
                value="{{ old('student_id', $user->student_id) }}"
                required
                class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
            >
        </div>


        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">
                Full Name
            </label>

            <input
                type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
            >
        </div>


        <div>
            <label class="mb-2 block text-sm font-bold text-slate-700">
                Email
            </label>

            <input
                type="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
                class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
            >
        </div>

        <div>
    <label class="mb-2 block text-sm font-bold text-slate-700">
        Programme
    </label>

    <select
        name="programme"
        required
        class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
    >
        <option value="SVM"
            @selected(old('programme', $user->programme) === 'SVM')>
            SVM
        </option>

        <option value="DVM"
            @selected(old('programme', $user->programme) === 'DVM')>
            DVM
        </option>
    </select>

    @error('programme')
        <p class="mt-2 text-sm font-semibold text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>


        <div class="grid gap-5 sm:grid-cols-2">

            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                    Course
                </label>

                <select
                    name="course"
                    required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
                >
                    @foreach(['KPD', 'BAK', 'HSK', 'HBP', 'BPM', 'KMK', 'OPP'] as $course)
                        <option
                            value="{{ $course }}"
                            @selected(old('course', $user->course) === $course)
                        >
                            {{ $course }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div>
                <label class="mb-2 block text-sm font-bold text-slate-700">
                    Semester
                </label>

                <select
                    name="semester"
                    required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
                >
                    @foreach([1, 2, 3, 4] as $semester)
                        <option
                            value="{{ $semester }}"
                            @selected((string) old('semester', $user->semester) === (string) $semester)
                        >
                            Semester {{ $semester }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>


        <div class="flex flex-wrap gap-3 pt-3">

            <button
                type="submit"
                class="admin-button"
            >
                Save Changes
            </button>

            <a
                href="{{ route('admin.students.show', $user) }}"
                class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50"
            >
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection