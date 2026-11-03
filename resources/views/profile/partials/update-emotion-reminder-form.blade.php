<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Evening Emotion Reminder</h2>
        <p class="mt-1 text-sm text-gray-600">Receive an optional in-app reminder at 8:30 PM Malaysia time if you have not checked in today. Reminders are off unless you enable them.</p>
    </header>
    <form method="POST" action="{{ route('profile.emotion-reminder.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('PATCH')
        <input type="hidden" name="evening_emotion_reminder" value="0">
        <label for="evening-emotion-reminder" class="flex cursor-pointer items-start gap-3 text-sm text-slate-700">
            <input id="evening-emotion-reminder" type="checkbox" name="evening_emotion_reminder" value="1" @checked(old('evening_emotion_reminder', $user->evening_emotion_reminder)) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-purple-700 focus:ring-purple-600">
            <span>Enable Evening Emotion Reminder</span>
        </label>
        @error('evening_emotion_reminder')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
        <p class="text-xs text-slate-500">Emotion check-ins are always optional. You can turn reminders off here at any time. This setting sends no email or phone notifications.</p>
        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="student-button">Save reminder preference</button>
            @if (session('status') === 'emotion-reminder-updated')<p role="status" class="text-sm font-semibold text-emerald-600">Reminder preference saved.</p>@endif
        </div>
    </form>
</section>
