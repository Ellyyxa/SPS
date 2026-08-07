<x-app-layout>

<div class="max-w-7xl mx-auto py-6">

    <h2>My Tasks</h2>

    @if(session('success'))
        <div>
            {{ session('success') }}
        </div>
    @endif


    <a href="{{ route('tasks.create') }}">
        Add New Task
    </a>


    <table border="1" cellpadding="10">

        <tr>
            <th>Title</th>
            <th>Due Date</th>
            <th>Priority</th>
            <th>Difficulty</th>
            <th>Score</th>
            <th>Status</th>
            <th>Action</th>
        </tr>


        @foreach($tasks as $task)

        <tr>

            <td>
                {{ $task->title }}
            </td>

            <td>
                {{ $task->due_date }}
            </td>

            <td>
                {{ $task->priority }}
            </td>

            <td>
                {{ $task->difficulty }}
            </td>

            <td>
            {{ $task->priority_score }}
            </td>

            <td>
                {{ $task->status }}
            </td>

            <td>

                <a href="{{ route('tasks.edit', $task->id) }}">
                    Edit
                </a>


                <form action="{{ route('tasks.destroy', $task->id) }}"
                      method="POST"
                      style="display:inline">

                    @csrf
                    @method('DELETE')

                    <button type="submit">
                        Delete
                    </button>

                </form>


                @if($task->status == 'Pending')

                <form action="{{ route('tasks.complete', $task->id) }}"
                      method="POST"
                      style="display:inline">

                    @csrf
                    @method('PATCH')

                    <button type="submit">
                        Complete
                    </button>

                </form>

                @endif

            </td>

        </tr>

        @endforeach


    </table>


</div>

</x-app-layout>