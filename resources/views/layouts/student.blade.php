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
                        <img
                        src="{{ asset('images/branding/sps-logo.png') }}"
                        alt="SPS Logo"
                        class="student-brand-logo"
                    >
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
                    @php($unreadNotificationCount = auth()->user()?->notifications()->where('is_read', false)->count() ?? 0)
                    <a href="{{ route('notifications.index') }}" class="student-nav-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}"><x-student-icon name="notifications" /><span>Notification</span>@if($unreadNotificationCount)<span class="student-notification-badge" aria-label="{{ $unreadNotificationCount }} unread notifications">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>@endif</a>
                    <a href="{{ route('moods.index') }}" class="student-nav-link {{ request()->routeIs('moods.*') ? 'is-active' : '' }}"><x-student-icon name="emotion" />Emotion</a>
                    <a href="{{ route('penguin') }}" class="student-nav-link {{ request()->routeIs('penguin') ? 'is-active' : '' }}"><x-student-icon name="penguin" />My Penguin</a>
                    <a href="{{ route('profile.edit') }}" class="student-nav-link {{ request()->routeIs('profile.*') ? 'is-active' : '' }}"><x-student-icon name="profile" />Profile</a>
                </nav>

                <div class="mt-auto border-t border-purple-800 px-4 py-5">
                    @php($sidebarUser = auth()->user())
                    <div class="mb-3 flex items-center gap-3 px-3">
                        <span class="profile-avatar h-10 w-10 rounded-xl border-purple-300 bg-purple-100 text-sm text-purple-800">
                            @if ($sidebarUser?->profile_photo_path)
                                <img src="{{ asset('storage/' . $sidebarUser->profile_photo_path) }}" alt="{{ $sidebarUser->name }}'s profile photo">
                            @else
                                {{ strtoupper(mb_substr($sidebarUser?->name ?? 'S', 0, 1)) }}
                            @endif
                        </span>
                        <p class="min-w-0 truncate text-sm font-medium text-purple-100">{{ $sidebarUser?->name ?? 'Student' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="student-nav-link w-full"><x-student-icon name="logout" />Logout</button>
                    </form>
                </div>
            </aside>

            <main class="min-h-screen md:ml-72">
    <header class="flex items-center justify-between px-5 py-4 md:hidden">

        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-extrabold text-indigo-950">
            <img
                src="{{ asset('images/branding/sps-logo.png') }}"
                alt="SPS Logo"
                class="student-brand-logo student-brand-logo-small"
            >
            SPS
        </a>

        <button
            type="button"
            class="rounded-xl bg-purple-800 px-4 py-2 text-sm font-bold text-white shadow"
            @click="mobileOpen = true"
        >
            Menu
        </button>

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
            @if (session('level_up'))
    @php($levelUp = session('level_up'))

    <div
        x-data="{ showLevelUp: true }"
        x-show="showLevelUp"
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-5"
        role="dialog"
        aria-modal="true"
        aria-labelledby="level-up-title"
    >
        <div
            x-show="showLevelUp"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="relative w-full max-w-md overflow-hidden rounded-[2rem] bg-white p-8 text-center shadow-2xl"
            @click.outside="showLevelUp = false"
        >
            <button
                type="button"
                @click="showLevelUp = false"
                class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 font-bold text-slate-500 hover:bg-slate-200"
                aria-label="Close level up message"
            >
                ✕
            </button>

            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <span class="absolute left-[12%] top-[15%] text-xl motion-safe:animate-bounce">✨</span>
                <span class="absolute right-[14%] top-[22%] text-lg motion-safe:animate-bounce">⭐</span>
                <span class="absolute bottom-[20%] left-[16%] text-lg motion-safe:animate-pulse">💜</span>
                <span class="absolute bottom-[18%] right-[16%] text-xl motion-safe:animate-pulse">✨</span>
            </div>

            <div class="relative">
                <p class="text-sm font-extrabold uppercase tracking-[.2em] text-purple-700">
                    Level Up!
                </p>

                <div class="mx-auto mt-4 flex h-44 w-44 items-center justify-center rounded-full bg-purple-50 p-3 ring-8 ring-purple-100/60">
                    <img
                        src="{{ asset('images/gamification/penguin-level-' . $levelUp['level'] . '.png') }}"
                        alt="Level {{ $levelUp['level'] }} penguin"
                        class="h-full w-full object-contain motion-safe:animate-[bounce_1s_ease-in-out_1]"
                    >
                </div>

                <h2 id="level-up-title" class="mt-5 text-3xl font-extrabold text-slate-900">
                    Level {{ $levelUp['level'] }}
                </h2>

                <p class="mt-1 text-xl font-extrabold text-purple-700">
                    {{ $levelUp['name'] }}
                </p>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Your Penguin evolved! Keep completing tasks and checking in to continue your progress.
                </p>

                <button
                    type="button"
                    @click="showLevelUp = false"
                    class="student-button mt-6 w-full justify-center"
                >
                    Continue
                </button>
            </div>
        </div>
    </div>
@endif
        </div>
    </body>
</html>
