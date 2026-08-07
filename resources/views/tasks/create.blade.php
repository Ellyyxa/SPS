<x-app-layout>

<div class="max-w-7xl mx-auto py-6">

<h2>Add New Task</h2>

<form method="POST" action="{{ route('tasks.store') }}">

@csrf

<div>
<label>Title</label>
<input type="text" name="title">
</div>


<div>
<label>Description</label>
<textarea name="description"></textarea>
</div>


<div>
<label>Due Date</label>
<input type="date" name="due_date">
</div>


<div>
<label>Priority</label>

<select name="priority">

<option value="Low">Low</option>
<option value="Medium">Medium</option>
<option value="High">High</option>

</select>

</div>


<div>
<label>Difficulty</label>

<select name="difficulty">

<option value="1">Easy</option>
<option value="2">Medium</option>
<option value="3">Hard</option>

</select>

</div>


<button type="submit">
Save Task
</button>


</form>

</div>

</x-app-layout>