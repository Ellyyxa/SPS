<section class="space-y-6" x-data="{ open: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }" x-init="$watch('open', value => document.body.classList.toggle('student-modal-open', value))">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button type="button" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-rose-700" @click="open = true">{{ __('Delete Account') }}</button>

    <div x-cloak x-show="open" x-transition.opacity class="student-modal" role="dialog" aria-modal="true" aria-labelledby="delete-account-heading" @keydown.escape.window="open = false">
        <div class="student-modal-backdrop" @click="open = false"></div>
        <form method="post" action="{{ route('profile.destroy') }}" class="student-modal-panel" @click.stop>
            @csrf
            @method('delete')

            <h2 id="delete-account-heading" class="text-xl font-extrabold text-slate-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" class="student-button-secondary" @click="open = false">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-rose-700">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </div>
</section>
