<x-app-layout>

<div class="p-6">

@if(auth()->user()->role == 'admin')

    <h1 class="text-2xl font-bold">
        Admin Dashboard
    </h1>

    <p>
        Selamat datang Admin SPS
    </p>

@else

    <h1 class="text-2xl font-bold">
        Student Dashboard
    </h1>

    <p>
        Selamat datang {{ auth()->user()->name }}
    </p>

@endif

</div>

</x-app-layout>