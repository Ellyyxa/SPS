<x-app-layout>

<div class="p-6">

    <h1 class="text-2xl font-bold mb-4">
        Add New Task
    </h1>


    <form method="POST" action="{{ route('tasks.store') }}">

        @csrf


        <div class="mb-3">
            <label>Task Title</label>

            <input type="text"
                   name="title"
                   class="border rounded w-full p-2">
        </div>


        <div class="mb-3">
            <label>Description</label>

            <textarea name="description"
                      class="border rounded w-full p-2"></textarea>
        </div>


        <div class="mb-3">
            <label>Due Date</label>

            <input type="date"
                   name="due_date"
                   class="border rounded w-full p-2">
        </div>


        <div class="mb-3">
            <label>Difficulty</label>

            <select name="difficulty"
                    class="border rounded w-full p-2">

                <option value="1">Easy</option>
                <option value="2">Medium</option>
                <option value="3">Hard</option>

            </select>
        </div>


        <div class="mb-3">
            <label>Priority</label>

            <select name="priority"
                    class="border rounded w-full p-2">

                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>

            </select>
        </div>


        <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded">

            Save Task

        </button>


    </form>

</div>

</x-app-layout>