<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ config('app.name', 'SPS') }}</title><link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="auth-page font-sans antialiased"><main class="auth-stage"><a href="/" class="auth-brand" aria-label="SPS home"><span class="auth-brand-mark">S</span><span><strong>SPS</strong><small>Sistem Produktiviti Pelajar</small></span></a><div class="auth-panel">{{ $slot }}</div></main></body>
</html>
