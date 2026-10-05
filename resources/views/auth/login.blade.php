<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Log in · {{ config('app.name', 'SPS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page font-sans antialiased">
    <main class="login-shell">
        <section class="login-promo" aria-labelledby="login-promo-title">
            <div class="login-promo-content">
                <a href="{{ url('/') }}" class="login-promo-brand" aria-label="SPS home">
                    <img src="{{ asset('images/branding/sps-logo.png') }}" alt="SPS Student Productivity System">
                </a>
                <p class="login-promo-school">Kolej Vokasional Kuala Selangor</p>
                <h1 id="login-promo-title">Small steps.<br>Brighter progress.</h1>
                <p class="login-promo-copy">Manage your tasks, understand your emotions, and build productive habits with your Penguin Companion.</p>
                <ul class="login-promo-points" aria-label="SPS benefits">
                    <li><strong>Focus</strong><span>Plan with clarity</span></li>
                    <li><strong>Grow</strong><span>Build good habits</span></li>
                    <li><strong>Thrive</strong><span>Celebrate progress</span></li>
                </ul>
            </div>
            <img class="login-promo-penguin" src="{{ asset('images/gamification/penguin-level-1.png') }}" alt="" aria-hidden="true">
        </section>

        <section class="login-form-area" aria-labelledby="login-title">
            <div class="login-form-card">
                <a href="{{ url('/') }}" class="login-mobile-brand" aria-label="SPS home"><img src="{{ asset('images/branding/sps-logo.png') }}" alt="SPS Student Productivity System"></a>
                <p class="login-eyebrow">Welcome back</p>
                <h2 id="login-title">Sistem Produktiviti Pelajar</h2>
                <p class="login-intro">Sign in using your college account to continue.</p>
                <x-auth-session-status class="auth-status" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="auth-form login-form" data-loading>
                    @csrf
                    <div><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
                    <div><label for="password">Password</label><div class="auth-password"><input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"><button type="button" data-password-toggle="#password" aria-label="Show password">Show</button></div>@error('password')<p class="auth-error">{{ $message }}</p>@enderror</div>
                    <div class="login-form-options"><div class="auth-check"><input id="remember_me" type="checkbox" name="remember" value="1"><label for="remember_me">Remember me</label></div>@if (Route::has('password.request'))<a href="{{ route('password.request') }}">Forgot password?</a>@endif</div>
                    <button type="submit" class="auth-submit">Login to SPS</button>
                </form>
                <p class="login-managed-note">Accounts are provided and managed by the Counselling Unit.</p>
            </div>
        </section>
    </main>
</body>
</html>
