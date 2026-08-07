<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Student ID -->
<div>
    <x-input-label for="student_id" :value="__('Student ID')" />

    <x-text-input id="student_id"
                  class="block mt-1 w-full"
                  type="text"
                  name="student_id"
                  :value="old('student_id')"
                  required
                  autofocus
                  autocomplete="student_id" />

    <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
</div>

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Course -->
<div class="mt-4">
    <x-input-label for="course" :value="__('Course')" />

    <x-text-input id="course"
                  class="block mt-1 w-full"
                  type="text"
                  name="course"
                  :value="old('course')"
                  required
                  autocomplete="course" />

    <x-input-error :messages="$errors->get('course')" class="mt-2" />
</div>

        <!-- Semester -->
<div class="mt-4">
    <x-input-label for="semester" :value="__('Semester')" />

    <select id="semester"
            name="semester"
            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
            required>

        <option value="">Select Semester</option>
        <option value="1">Semester 1</option>
        <option value="2">Semester 2</option>
        <option value="3">Semester 3</option>
        <option value="4">Semester 4</option>

    </select>

    <x-input-error :messages="$errors->get('semester')" class="mt-2" />
</div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
