<x-app-layout>

<div class="p-6">

    <h1 class="text-2xl font-bold mb-4">
        Edit Task
    </h1>


    <form method="POST" action="{{ route('tasks.update', $task->id) }}">

        @csrf
        @method('PUT')


        <div class="mb-3">

            <label>Task Title</label>

            <input type="text"
                   name="title"
                   value="{{ $task->title }}"
                   class="border rounded w-full p-2">

        </div>


        <div class="mb-3">

            <label>Description</label>

            <textarea name="description"
                      class="border rounded w-full p-2">{{ $task->description }}</textarea>

        </div>


        <div class="mb-3">

            <label>Due Date</label>

            <input type="date"
                   name="due_date"
                   value="{{ $task->due_date }}"
                   class="border rounded w-full p-2">

        </div>


        <div class="mb-3">

            <label>Priority</label>

            <select name="priority"
                    class="border rounded w-full p-2">

                <option value="Low"
                {{ $task->priority == 'Low' ? 'selected' : '' }}>
                    Low
                </option>

                <option value="Medium"
                {{ $task->priority == 'Medium' ? 'selected' : '' }}>
                    Medium
                </option>

                <option value="High"
                {{ $task->priority == 'High' ? 'selected' : '' }}>
                    High
                </option>

            </select>

        </div>


        <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded">

            Update Task

        </button>


    </form>

</div>

</x-app-layout>