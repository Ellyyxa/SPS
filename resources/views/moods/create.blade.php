<x-app-layout>

<div class="max-w-7xl mx-auto py-6">

<h2>Update Today's Mood</h2>


<form method="POST" action="{{ route('moods.store') }}">

@csrf


<div>

<label>Mood</label>

<select name="mood">

<option value="Happy">Happy 😊</option>
<option value="Neutral">Neutral 😐</option>
<option value="Sad">Sad 😢</option>
<option value="Stress">Stress 😰</option>
<option value="Angry">Angry 😡</option>

</select>

</div>


<div>

<label>Note</label>

<textarea name="note"
placeholder="How are you feeling today?"></textarea>

</div>


<button type="submit">
Save Mood
</button>


</form>


</div>

</x-app-layout>