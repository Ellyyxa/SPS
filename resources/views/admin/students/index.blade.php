@extends('layouts.admin')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">
            Directory
        </p>

        <h1 class="student-page-title mt-1">
            Student Accounts
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Manage and view registered student accounts.
        </p>
    </div>

    <a href="{{ route('admin.students.create') }}" class="admin-button">
        + Add Student
    </a>
</div>


{{-- FILTER --}}
<div class="mb-6 rounded-3xl bg-white p-5 shadow-sm">
    <form
        method="GET"
        action="{{ route('admin.students.index') }}"
        data-admin-filter
        class="grid gap-4 md:grid-cols-5"
    >

        {{-- Search --}}
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-semibold text-slate-700">
                Search Student
            </label>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Name or Student ID"
                data-filter-search
                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
            >
        </div>

        <div>
    <label class="mb-2 block text-sm font-semibold text-slate-700">
        Programme
    </label>

    <select
        name="programme"
        onchange="this.form.submit()"
        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
    >
        <option value="">All Programmes</option>

        <option value="SVM" @selected(request('programme') === 'SVM')>
            SVM
        </option>

        <option value="DVM" @selected(request('programme') === 'DVM')>
            DVM
        </option>
    </select>
</div>


        {{-- Course --}}
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">
                Course
            </label>

            <select
                name="course"
                onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
            >
                <option value="">All Courses</option>

                @foreach($courses as $course)
                    <option
                        value="{{ $course }}"
                        @selected(request('course') == $course)
                    >
                        {{ $course }}
                    </option>
                @endforeach
            </select>
        </div>


        {{-- Semester --}}
        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-700">
                Semester
            </label>

            <select
                name="semester"
                onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-100"
            >
                <option value="">All Semesters</option>

                @foreach($semesters as $semester)
                    <option
                        value="{{ $semester }}"
                        @selected(request('semester') == $semester)
                    >
                        Semester {{ $semester }}
                    </option>
                @endforeach
            </select>
        </div>

    </form>

    @if(
    request()->filled('search') ||
    request()->filled('programme') ||
    request()->filled('course') ||
    request()->filled('semester')
)
        <div class="mt-4">
            <a
                href="{{ route('admin.students.index') }}"
                class="text-sm font-semibold text-purple-700 hover:underline"
            >
                Clear Filters
            </a>
        </div>
    @endif
</div>


{{-- STUDENT TABLE --}}
<div class="overflow-hidden rounded-3xl bg-white shadow-sm">

    <div class="border-b border-slate-100 px-6 py-5">
        <div class="flex items-center justify-between gap-4">

            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    Student List
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $students->total() }} student(s) found
                </p>
            </div>

        </div>
    </div>


    @if($students->count())

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">

                        <th class="px-6 py-4">
                            Student
                        </th>

                        <th class="px-6 py-4">
                            Student ID
                        </th>

                        <th class="px-6 py-4">
                            Course
                        </th>

                        <th class="px-6 py-4">
                            Semester
                        </th>

                        <th class="px-6 py-4">
                            Tasks
                        </th>

                        <th class="px-6 py-4">
                            Completed
                        </th>

                        <th class="px-6 py-4 text-right">
                            Action
                        </th>

                    </tr>
                </thead>


                <tbody class="divide-y divide-slate-100">

                    @foreach($students as $student)

                        <tr class="transition hover:bg-purple-50/40">

                            {{-- Student --}}
                            <td class="px-6 py-5">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-purple-100 font-bold text-purple-700">

                                        @if($student->profile_photo_path)

                                            <img
                                                src="{{ asset('storage/'.$student->profile_photo_path) }}"
                                                alt="{{ $student->name }}"
                                                class="h-full w-full object-cover"
                                            >

                                        @else

                                            {{ strtoupper(mb_substr($student->name, 0, 1)) }}

                                        @endif

                                    </div>


                                    <div class="min-w-0">

                                        <p class="font-semibold text-slate-900">
                                            {{ $student->name }}
                                        </p>

                                        <p class="truncate text-sm text-slate-500">
                                            {{ $student->email }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            {{-- Student ID --}}
                            <td class="px-6 py-5 text-sm text-slate-700">
                                {{ $student->student_id ?? '-' }}
                            </td>


                            {{-- Course --}}
                            <td class="px-6 py-5 text-sm text-slate-700">
                                {{ $student->course ?? '-' }}
                            </td>


                            {{-- Semester --}}
                            <td class="px-6 py-5 text-sm text-slate-700">

                                @if($student->semester)
                                    Semester {{ $student->semester }}
                                @else
                                    -
                                @endif

                            </td>


                            {{-- Tasks --}}
                            <td class="px-6 py-5">

                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                    {{ $student->tasks_count }}
                                </span>

                            </td>


                            {{-- Completed --}}
                            <td class="px-6 py-5">

                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                    {{ $student->completed_tasks }}
                                </span>

                            </td>


                            {{-- Action --}}
                            <td class="px-6 py-5 text-right">

                                <a
                                    href="{{ route('admin.students.show', $student) }}"
                                    class="inline-flex items-center rounded-xl bg-purple-50 px-4 py-2 text-sm font-bold text-purple-700 transition hover:bg-purple-100"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}
        <div class="border-t border-slate-100 px-6 py-4">
            {{ $students->links() }}
        </div>

    @else

        {{-- EMPTY STATE --}}
        <div class="px-6 py-16 text-center">

            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-purple-50 text-2xl">
                👤
            </div>

            <h3 class="font-bold text-slate-900">
                No students found
            </h3>

            <p class="mt-2 text-sm text-slate-500">
                Try changing the search or filter options.
            </p>

            @if(request()->filled('search') || request()->filled('course') || request()->filled('semester'))

                <a
                    href="{{ route('admin.students.index') }}"
                    class="mt-4 inline-block text-sm font-bold text-purple-700 hover:underline"
                >
                    Clear Filters
                </a>

            @endif

        </div>

    @endif

</div>

@endsection