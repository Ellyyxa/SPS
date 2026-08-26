<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'SPS') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="student-shell font-sans antialiased">
        <div x-data="{ mobileOpen: false }" class="min-h-screen">
            <div x-show="mobileOpen" x-transition.opacity class="fixed inset-0 z-30 bg-slate-950/40 md:hidden" @click="mobileOpen = false"></div>

            <aside class="student-sidebar fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col transition-transform duration-300 md:translate-x-0" :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'">
                <div class="flex items-center justify-between px-6 pt-7">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3" aria-label="SPS dashboard">
                        <span class="student-brand-mark" aria-hidden="true">S</span>
                        <span>
                            <span class="block text-3xl font-extrabold tracking-tight text-white">SPS</span>
                            <span class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-purple-200">Student Productivity</span>
                        </span>
                    </a>
                    <button type="button" class="rounded-lg p-2 text-purple-100 md:hidden" @click="mobileOpen = false" aria-label="Close menu">✕</button>
                </div>

                <nav class="mt-10 space-y-2 px-4" aria-label="Student navigation">
                    <a href="{{ route('dashboard') }}" class="student-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"><x-student-icon name="dashboard" />Dashboard</a>
                    <a href="{{ route('tasks.index') }}" class="student-nav-link {{ request()->routeIs('tasks.*') ? 'is-active' : '' }}"><x-student-icon name="tasks" />Task</a>
                    <a href="{{ route('calendar') }}" class="student-nav-link {{ request()->routeIs('calendar') ? 'is-active' : '' }}"><x-student-icon name="calendar" />Calendar</a>
                    <a href="{{ route('notifications.index') }}" class="student-nav-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}"><x-student-icon name="notifications" />Notification</a>
                    <a href="{{ route('moods.index') }}" class="student-nav-link {{ request()->routeIs('moods.*') ? 'is-active' : '' }}"><x-student-icon name="emotion" />Emotion</a>
                    <a href="{{ route('penguin') }}" class="student-nav-link {{ request()->routeIs('penguin') ? 'is-active' : '' }}"><x-student-icon name="penguin" />My Penguin</a>
                    <a href="{{ route('profile.edit') }}" class="student-nav-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}"><x-student-icon name="profile" />Profile</a>
                </nav>

                <div class="mt-auto border-t border-purple-800 px-4 py-5">
                    <p class="mb-3 truncate px-4 text-sm font-medium text-purple-200">{{ auth()->user()->name ?? 'Student' }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="student-nav-link w-full"><x-student-icon name="logout" />Logout</button>
                    </form>
                </div>
            </aside>

            <main class="min-h-screen md:ml-72">
                <header class="flex items-center justify-between px-5 py-4 md:hidden">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-extrabold text-indigo-950"><span class="student-brand-mark student-brand-mark-small" aria-hidden="true">S</span>SPS</a>
                    <button type="button" class="rounded-xl bg-purple-800 px-4 py-2 text-sm font-bold text-white shadow" @click="mobileOpen = true">Menu</button>
                </header>

                <div class="mx-auto max-w-7xl px-5 pb-10 pt-4 sm:px-8 md:pt-10 lg:px-10">
                    @if (session('success'))
                        <div class="student-flash student-flash-success" data-auto-dismiss>{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="student-flash student-flash-error" data-auto-dismiss>{{ session('error') }}</div>
                    @endif

                    @hasSection('content')
                        @yield('content')
                    @elseif (isset($slot))
                        {{ $slot }}
                    @endif
                </div>
            </main>
        </div>
    </body>
</html>
