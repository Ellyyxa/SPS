<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="profile_photo" :value="__('Profile Photo')" />
            <div class="profile-photo-picker mt-2">
                <span class="profile-avatar" data-photo-fallback>
                    @if ($user->profile_photo_path)
                        <img data-photo-preview src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Current profile photo">
                    @else
                        <img data-photo-preview class="hidden" alt="Selected profile photo preview">
                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                    @endif
                </span>
                <div class="min-w-0"><input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" data-photo-input data-photo-preview="[data-photo-preview]" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-purple-100 file:px-3 file:py-2 file:text-sm file:font-bold file:text-purple-800 hover:file:bg-purple-200"><p class="mt-2 text-xs text-slate-500">JPG, PNG, or WEBP up to 2 MB.</p></div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
        </div>

        <div>
            <x-input-label for="student_id" :value="__('Student ID')" />
            <x-text-input id="student_id" type="text" class="mt-1 block w-full bg-slate-100 text-slate-500" :value="$user->student_id" readonly />
            <p class="mt-1 text-xs text-slate-500">Your student ID is managed by the system.</p>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="course" :value="__('Course / Program')" />
            <x-text-input id="course" name="course" type="text" class="mt-1 block w-full" :value="old('course', $user->course)" required autocomplete="organization-title" />
            <x-input-error class="mt-2" :messages="$errors->get('course')" />
        </div>

        <div>
            <x-input-label for="semester" :value="__('Semester')" />
            <select id="semester" name="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                @for ($semester = 1; $semester <= 8; $semester++)
                    <option value="{{ $semester }}" @selected(old('semester', $user->semester) == $semester)>Semester {{ $semester }}</option>
                @endfor
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('semester')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
