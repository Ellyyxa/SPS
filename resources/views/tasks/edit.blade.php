<x-app-layout>

<div class="max-w-7xl mx-auto py-6">

<h2>Edit Task</h2>


<form method="POST" action="{{ route('tasks.update', $task->id) }}">

@csrf
@method('PUT')


<div>
<label>Title</label>

<input type="text"
       name="title"
       value="{{ $task->title }}">
</div>



<div>
<label>Description</label>

<textarea name="description">{{ $task->description }}</textarea>

</div>



<div>
<label>Due Date</label>

<input type="date"
       name="due_date"
       value="{{ $task->due_date }}">

</div>



<div>
<label>Priority</label>

<select name="priority">

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



<div>
<label>Difficulty</label>

<select name="difficulty">

<option value="1"
{{ $task->difficulty == 1 ? 'selected' : '' }}>
Easy
</option>


<option value="2"
{{ $task->difficulty == 2 ? 'selected' : '' }}>
Medium
</option>


<option value="3"
{{ $task->difficulty == 3 ? 'selected' : '' }}>
Hard
</option>


</select>

</div>



<button type="submit">
Update Task
</button>


</form>

</div>

</x-app-layout>