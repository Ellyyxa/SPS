<x-app-layout>

<div class="p-6">
     
@if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

    <h1 class="text-2xl font-bold mb-4">
        My Tasks
    </h1>


    <a href="{{ route('tasks.create') }}"
       class="bg-blue-500 text-white px-4 py-2 rounded">
        + Add Task
    </a>


    <div class="mt-6">

        @foreach($tasks as $task)

            <div class="border p-4 mb-3 rounded">

        <h3 class="font-bold">
        {{ $task->title }}
        </h3>

        <p>
        Due: {{ $task->due_date }}
        </p>

        <p>
        Priority: {{ $task->priority }}
        </p>

        <p>
    Status:

    @if($task->status == 'Completed')
        <span class="bg-green-100 text-green-700 px-2 py-1 rounded">
            ✅ Completed
        </span>
    @else
        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded">
            ⏳ Pending
        </span>
    @endif

</p>

        @if($task->status == 'Pending')

<form action="{{ route('tasks.complete', $task->id) }}"
      method="POST"
      style="display:inline;">

    @csrf
    @method('PATCH')

    <button type="submit"
            class="bg-green-500 text-white px-3 py-1 rounded">

        Complete

    </button>

</form>

@endif

        <a href="{{ route('tasks.edit', $task->id) }}"
        class="bg-yellow-500 text-white px-3 py-1 rounded">

        Edit

        </a>

        <form action="{{ route('tasks.destroy', $task->id) }}"
      method="POST"
      style="display:inline;">

    @csrf
    @method('DELETE')

    <button type="submit"
            onclick="return confirm('Are you sure you want to delete this task?')"
            class="bg-red-500 text-white px-3 py-1 rounded">

        Delete

    </button>

</form>

        </div>

        @endforeach


    </div>

</div>

</x-app-layout>