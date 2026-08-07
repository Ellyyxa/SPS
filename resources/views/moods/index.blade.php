<x-app-layout>

<div class="max-w-7xl mx-auto py-6">

<h2>My Mood History</h2>


<a href="{{ route('moods.create') }}">
Update Mood
</a>


<table border="1" cellpadding="10">

<tr>

<th>Date</th>
<th>Mood</th>
<th>Note</th>

</tr>


@foreach($moods as $mood)

<tr>

<td>
{{ $mood->date }}
</td>


<td>
{{ $mood->mood }}
</td>


<td>
{{ $mood->note }}
</td>


</tr>

@endforeach


</table>


</div>

</x-app-layout>