@extends('layouts.admin')

@section('content')
<div class="mb-8">
    <p class="text-sm font-bold uppercase tracking-[.18em] text-blue-700">Administrator account</p>
    <h1 class="student-page-title mt-1">My Profile</h1>
</div>

<div class="grid gap-6 xl:grid-cols-[.75fr_1.25fr]">
    <aside class="admin-card p-7">
        <div class="profile-avatar h-24 w-24 text-3xl">
            @if ($user->profile_photo_path)
                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}'s profile photo">
            @else
                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
            @endif
        </div>

        <h2 class="mt-5 text-xl font-extrabold text-slate-900">{{ $user->name }}</h2>
        <p class="mt-1 text-sm font-bold text-purple-800">Administrator</p>

        <dl class="mt-6 space-y-3 text-sm">
            <div><dt class="font-bold text-slate-500">Email</dt><dd class="break-words text-slate-800">{{ $user->email }}</dd></div>
            <div><dt class="font-bold text-slate-500">Account role</dt><dd class="text-slate-800">Administrator</dd></div>
        </dl>
    </aside>

    <div class="space-y-6">
        <section class="admin-card p-6 sm:p-8">
            <header>
                <h2 class="text-lg font-extrabold text-slate-900">Profile Information</h2>
                <p class="mt-1 text-sm text-slate-600">Update your administrator account details and profile photo.</p>
            </header>

            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="profile_photo" class="mb-2 block text-sm font-bold text-slate-700">Profile Photo</label>
                    <div class="profile-photo-picker">
                        <span class="profile-avatar" data-photo-fallback>
                            @if ($user->profile_photo_path)
                                <img data-photo-preview src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Current profile photo">
                            @else
                                <img data-photo-preview class="hidden" alt="Selected profile photo preview">
                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                            @endif
                        </span>
                        <div class="min-w-0">
                            <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" data-photo-input data-photo-preview="[data-photo-preview]" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-purple-100 file:px-3 file:py-2 file:text-sm file:font-bold file:text-purple-800 hover:file:bg-purple-200">
                            <p class="mt-2 text-xs text-slate-500">JPG, PNG, or WEBP up to 2 MB.</p>
                        </div>
                    </div>
                    @error('profile_photo')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div><label for="name" class="mb-2 block text-sm font-bold text-slate-700">Name</label><input id="name" class="admin-input" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">@error('name')<p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror</div>
                    <div><label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email</label><input id="email" class="admin-input" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">@error('email')<p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror</div>
                </div>

                <div class="flex items-center gap-4">
                    <button class="admin-button">Save Profile</button>
                    @if (session('status') === 'profile-updated')<p class="text-sm font-bold text-emerald-600">Profile updated.</p>@endif
                </div>
            </form>
        </section>

        <section class="admin-card p-6 sm:p-8">
            <header>
                <h2 class="text-lg font-extrabold text-slate-900">Change Password</h2>
                <p class="mt-1 text-sm text-slate-600">Ensure your administrator account uses a long, secure password.</p>
            </header>

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('PUT')
                <div><label for="admin_current_password" class="mb-2 block text-sm font-bold text-slate-700">Current Password</label><input id="admin_current_password" name="current_password" type="password" autocomplete="current-password" class="admin-input" required>@error('current_password', 'updatePassword')<p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="admin_password" class="mb-2 block text-sm font-bold text-slate-700">New Password</label><input id="admin_password" name="password" type="password" autocomplete="new-password" class="admin-input" required>@error('password', 'updatePassword')<p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="admin_password_confirmation" class="mb-2 block text-sm font-bold text-slate-700">Confirm New Password</label><input id="admin_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="admin-input" required>@error('password_confirmation', 'updatePassword')<p class="mt-1 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror</div>
                <div class="flex items-center gap-4">
                    <button class="admin-button">Update Password</button>
                    @if (session('status') === 'password-updated')<p class="text-sm font-bold text-emerald-600">Password updated.</p>@endif
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
