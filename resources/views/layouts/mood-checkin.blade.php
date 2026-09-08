<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Daily Emotion Check-in - SPS</title><link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="auth-page font-sans antialiased"><main class="mood-stage"><header class="mood-brand"><span class="student-brand-mark">S</span><span><strong>SPS</strong><small>Daily emotion check-in</small></span><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form></header><div class="mood-panel">@hasSection('content')@yield('content')@elseif(isset($slot)){{ $slot }}@endif</div></main></body>
</html>
