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
    </div>

    <a href="{{ route('admin.students.create') }}" class="admin-button">
        + Add Student
    </a>
</div>
@endsection
