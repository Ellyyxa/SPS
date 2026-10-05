<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ $title ?? 'SPS Admin' }}</title><link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="admin-shell font-sans antialiased"><div x-data="{ mobileOpen: false }" class="min-h-screen"><div x-show="mobileOpen" x-transition.opacity class="fixed inset-0 z-30 bg-slate-950/50 md:hidden" @click="mobileOpen=false"></div><aside class="admin-sidebar fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col transition-transform duration-300 md:translate-x-0" :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"><div class="flex items-center justify-between px-6 pt-7"><a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
    <img
        src="{{ asset('images/branding/sps-logo.png') }}"
        alt="SPS Logo"
        class="h-14 w-14 object-contain"
    >

    <span>
        <span class="block text-3xl font-extrabold tracking-tight text-white">
            SPS
        </span>

        <span class="block text-[10px] font-bold uppercase tracking-[.14em] text-purple-200">
            Admin Console
        </span>
    </span>
</a>
<button class="p-2 text-white md:hidden" @click="mobileOpen=false">✕</button></div>
<nav class="mt-10 space-y-2 px-4"><a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
    <x-student-icon name="dashboard" />Dashboard</a>
    <a href="{{ route('admin.students.index') }}" class="admin-nav-link {{ request()->routeIs('admin.students.*') ? 'is-active' : '' }}">
        <x-student-icon name="profile" />Student Accounts</a>
        <a href="{{ route('admin.emotions.index') }}" class="admin-nav-link {{ request()->routeIs('admin.emotions.*') ? 'is-active' : '' }}">
            <x-student-icon name="emotion" />Emotion Analysis</a>
            <a href="{{ route('admin.productivity') }}" class="admin-nav-link {{ request()->routeIs('admin.productivity') ? 'is-active' : '' }}">
                <x-student-icon name="tasks" />Productivity / Reports</a><a href="{{ route('admin.notifications.index') }}" class="admin-nav-link {{ request()->routeIs('admin.notifications.*') ? 'is-active' : '' }}">
                    <x-student-icon name="notifications" />Notifications</a><a href="{{ route('admin.profile.edit') }}" class="admin-nav-link {{ request()->routeIs('admin.profile.*') ? 'is-active' : '' }}"><x-student-icon name="profile" />Profile</a>
                </nav><div class="mt-auto border-t border-purple-900 px-5 py-5"><div class="mb-3 flex items-center gap-3"><span class="admin-avatar h-10 w-10">@if(auth()->user()->profile_photo_path)<img src="{{ asset('storage/'.auth()->user()->profile_photo_path) }}" alt="Profile photo">@else{{ strtoupper(mb_substr(auth()->user()->name,0,1)) }}@endif</span><p class="min-w-0 truncate text-sm font-bold text-purple-100">{{ auth()->user()->name }}</p></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="admin-nav-link w-full"><x-student-icon name="logout" />Logout</button></form></div></aside><main class="min-h-screen md:ml-72"><header class="flex items-center justify-between px-5 py-4 md:hidden"><strong class="text-xl text-indigo-950">SPS Admin</strong><button class="admin-button" @click="mobileOpen=true">Menu</button></header><div class="mx-auto max-w-7xl px-5 pb-10 pt-5 sm:px-8 md:pt-10 lg:px-10">@if(session('success'))<div class="student-flash student-flash-success">{{ session('success') }}</div>@endif @if(session('error'))<div class="student-flash student-flash-error">{{ session('error') }}</div>@endif @yield('content')</div></main></div></body>
</html>
