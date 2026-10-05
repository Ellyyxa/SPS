<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
        $students = User::where('role', 'student')
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($inner) => $inner->where('name', 'like', '%'.$request->string('search').'%')->orWhere('student_id', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('programme'),fn ($query) => $query->where('programme', $request->string('programme')))
            ->when($request->filled('course'), fn ($query) => $query->where('course', $request->string('course')))
            ->when($request->filled('semester'), fn ($query) => $query->where('semester', $request->string('semester')))
            ->withCount(['tasks', 'tasks as completed_tasks' => fn ($query) => $query->where('status', 'Completed')])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $courses = User::where('role', 'student')->whereNotNull('course')->distinct()->orderBy('course')->pluck('course');
        $semesters = User::where('role', 'student')->whereNotNull('semester')->distinct()->orderBy('semester')->pluck('semester');

        return view('admin.students.index', compact('students', 'courses', 'semesters'));
    }

    public function create()
{
    return view('admin.students.create');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'student_id' => ['required', 'string', 'max:255', 'unique:users,student_id'],
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
        'programme' => ['required', 'in:SVM,DVM'],
        'course' => ['required', 'string', 'max:255'],
        'semester' => ['required', 'string', 'max:255'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $student = User::create([
        'student_id' => $validated['student_id'],
        'name' => $validated['name'],
        'email' => $validated['email'],
        'programme' => $validated['programme'],
        'course' => $validated['course'],
        'semester' => $validated['semester'],
        'password' => Hash::make($validated['password']),
        'role' => 'student',
    ]);

    return redirect()
        ->route('admin.students.show', $student)
        ->with('success', 'Student account created successfully.');
}

    public function show(User $user)
    {
        abort_unless($user->role === 'student', 404);

        $user->loadCount([
            'tasks',
            'tasks as completed_tasks' => fn ($query) => $query->where('status', 'Completed'),
            'tasks as pending_tasks' => fn ($query) => $query->where('status', 'Pending'),
        ]);
        $moods = $user->moods()->latest('date')->latest('id')->take(12)->get();

        return view('admin.students.show', compact('user', 'moods'));
    }

    public function edit(User $user)
{
    abort_unless($user->role === 'student', 404);

    return view('admin.students.edit', compact('user'));
}

public function update(Request $request, User $user)
{
    abort_unless($user->role === 'student', 404);

    $validated = $request->validate([
        'student_id' => [
            'required',
            'string',
            'max:255',
            'unique:users,student_id,' . $user->id,
        ],
        'name' => [
            'required',
            'string',
            'max:255',
        ],
        'email' => [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            'unique:users,email,' . $user->id,
        ],
        'programme' => [
    'required',
    'in:SVM,DVM',
],
        'course' => [
            'required',
            'string',
            'max:255',
        ],
        'semester' => [
            'required',
            'string',
            'max:255',
        ],
    ]);

    $user->update($validated);

    return redirect()
        ->route('admin.students.show', $user)
        ->with('success', 'Student account updated successfully.');
}
}
