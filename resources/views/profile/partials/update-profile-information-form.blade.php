<section>

    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Profile Information
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            View your account information and update your profile photo.
        </p>
    </header>


    <form
        method="POST"
        action="{{ route('profile.update') }}"
        enctype="multipart/form-data"
        class="mt-6 space-y-6"
    >
        @csrf
        @method('PATCH')


        {{-- Profile Photo --}}
        <div>

            <x-input-label
                for="profile_photo"
                :value="__('Profile Photo')"
            />

            <div class="profile-photo-picker mt-2">

                <span class="profile-avatar" data-photo-fallback>

                    @if ($user->profile_photo_path)

                        <img
                            data-photo-preview
                            src="{{ asset('storage/' . $user->profile_photo_path) }}"
                            alt="Current profile photo"
                        >

                    @else

                        <img
                            data-photo-preview
                            class="hidden"
                            alt="Selected profile photo preview"
                        >

                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}

                    @endif

                </span>


                <div class="min-w-0">

                    <input
                        id="profile_photo"
                        name="profile_photo"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                        data-photo-input
                        data-photo-preview="[data-photo-preview]"
                        class="block w-full text-sm text-slate-600
                               file:mr-4 file:rounded-lg file:border-0
                               file:bg-purple-100 file:px-3 file:py-2
                               file:text-sm file:font-bold file:text-purple-800
                               hover:file:bg-purple-200"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        JPG, PNG, or WEBP up to 2 MB.
                    </p>

                </div>

            </div>

            <x-input-error
                class="mt-2"
                :messages="$errors->get('profile_photo')"
            />

            @if ($errors->any())
    <div class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

        </div>


        <div class="border-t border-slate-100 pt-6">

            <h3 class="mb-4 font-bold text-slate-900">
                Account Information
            </h3>

            <div class="grid gap-5 sm:grid-cols-2">


                {{-- Student ID --}}
                <div>
                    <x-input-label :value="__('Student ID')" />

                    <x-text-input
                        type="text"
                        class="mt-1 block w-full bg-slate-100 text-slate-500"
                        :value="$user->student_id"
                        readonly
                    />
                </div>


                {{-- Name --}}
                <div>
                    <x-input-label :value="__('Full Name')" />

                    <x-text-input
                        type="text"
                        class="mt-1 block w-full bg-slate-100 text-slate-500"
                        :value="$user->name"
                        readonly
                    />
                </div>


                {{-- Email --}}
                <div>
                    <x-input-label :value="__('Email')" />

                    <x-text-input
                        type="text"
                        class="mt-1 block w-full bg-slate-100 text-slate-500"
                        :value="$user->email"
                        readonly
                    />
                </div>


                {{-- Programme --}}
                <div>
                    <x-input-label :value="__('Programme')" />

                    <x-text-input
                        type="text"
                        class="mt-1 block w-full bg-slate-100 text-slate-500"
                        :value="$user->programme ?: '-'"
                        readonly
                    />
                </div>


                {{-- Course --}}
                <div>
                    <x-input-label :value="__('Course')" />

                    <x-text-input
                        type="text"
                        class="mt-1 block w-full bg-slate-100 text-slate-500"
                        :value="$user->course ?: '-'"
                        readonly
                    />
                </div>


                {{-- Semester --}}
                <div>
                    <x-input-label :value="__('Semester')" />

                    <x-text-input
                        type="text"
                        class="mt-1 block w-full bg-slate-100 text-slate-500"
                        :value="$user->semester ? 'Semester '.$user->semester : '-'"
                        readonly
                    />
                </div>

            </div>

            <p class="mt-4 text-xs text-slate-500">
                Account and academic information is managed by the administrator.
            </p>

        </div>


        <div class="flex items-center gap-4">

            <x-primary-button>
                Save Profile Photo
            </x-primary-button>

            @if (session('status') === 'profile-updated')

                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-green-600"
                >
                    Saved.
                </p>

            @endif

        </div>

    </form>

</section>