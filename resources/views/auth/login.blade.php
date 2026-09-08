<x-guest-layout>
    <div class="auth-heading"><p class="auth-eyebrow">Welcome to SPS</p><h1>Log in to your account</h1><p>Plan your day and stay on track.</p></div>
    <x-auth-session-status class="auth-status" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="auth-form" data-loading>@csrf
        <div><label for="email">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com">@error('email')<p class="auth-error">{{ $message }}</p>@enderror</div>
        <div><label for="password">Password</label><div class="auth-password"><input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"><button type="button" data-password-toggle="#password" aria-label="Show password">Show</button></div>@error('password')<p class="auth-error">{{ $message }}</p>@enderror</div>
        <div class="auth-check"><input id="remember_me" type="checkbox" name="remember" value="1"><label for="remember_me">Remember me</label></div>
        <button type="submit" class="auth-submit">Log in</button>
        <div class="auth-links">@if(Route::has('password.request'))<a href="{{ route('password.request') }}">Forgot password?</a>@endif <span>New to SPS? <a href="{{ route('register') }}">Register here</a></span></div>
    </form>
</x-guest-layout>
