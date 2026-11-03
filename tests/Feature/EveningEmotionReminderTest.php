<?php

use App\Mail\TaskDueReminderMail;
use App\Models\GamificationProfile;
use App\Models\Mood;
use App\Models\Notification;
use App\Models\User;
use App\Models\XpTransaction;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-01 20:30:00', 'Asia/Kuala_Lumpur'));
    Mail::fake();
});

afterEach(function () {
    $this->travelBack();
});

test('reminder preference defaults to off in memory and in the database', function () {
    $student = User::factory()->create(['role' => 'student']);
    expect($student->evening_emotion_reminder)->toBeFalse()
        ->and($student->fresh()->evening_emotion_reminder)->toBeFalse();
    $this->actingAs($student)->get(route('profile.edit'))->assertOk()->assertSee('Evening Emotion Reminder');
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    expect(Notification::count())->toBe(0);
});

test('students can enable and disable their own reminder preference', function () {
    $student = User::factory()->create(['role' => 'student']);
    $this->actingAs($student)->patch(route('profile.emotion-reminder.update'), ['evening_emotion_reminder' => '1'])
        ->assertRedirect(route('profile.edit'))->assertSessionHas('status', 'emotion-reminder-updated');
    expect($student->fresh()->evening_emotion_reminder)->toBeTrue();
    $this->patch(route('profile.emotion-reminder.update'), ['evening_emotion_reminder' => '0'])
        ->assertRedirect(route('profile.edit'));
    expect($student->fresh()->evening_emotion_reminder)->toBeFalse();
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    expect(Notification::count())->toBe(0);
});

test('submitted ownership fields cannot change another students preference', function () {
    $student = User::factory()->create(['role' => 'student']);
    $other = User::factory()->create(['role' => 'student']);
    $this->actingAs($student)->patch(route('profile.emotion-reminder.update'), [
        'user_id' => $other->id, 'id' => $other->id, 'role' => 'admin', 'evening_emotion_reminder' => true,
    ])->assertRedirect(route('profile.edit'));
    expect($student->fresh()->evening_emotion_reminder)->toBeTrue()
        ->and($student->fresh()->role)->toBe('student')
        ->and($other->fresh()->evening_emotion_reminder)->toBeFalse();
});

test('guests and admins cannot update student reminder preferences', function () {
    $this->patch(route('profile.emotion-reminder.update'), ['evening_emotion_reminder' => 1])->assertRedirect(route('login'));
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->patch(route('profile.emotion-reminder.update'), ['evening_emotion_reminder' => 1])->assertForbidden();
    expect($admin->fresh()->evening_emotion_reminder)->toBeFalse();
});

test('invalid or missing preference is rejected without enabling reminders', function ($input) {
    $student = User::factory()->create(['role' => 'student']);
    $this->actingAs($student)->patch(route('profile.emotion-reminder.update'), $input)
        ->assertSessionHasErrors('evening_emotion_reminder');
    expect($student->fresh()->evening_emotion_reminder)->toBeFalse();
})->with([
    'missing' => [[]],
    'invalid value' => [['evening_emotion_reminder' => 'enabled']],
    'array value' => [['evening_emotion_reminder' => ['1']]],
]);

test('only opted-in students without todays mood receive a private unread reminder', function () {
    $eligible = User::factory()->create(['role' => 'student', 'evening_emotion_reminder' => true]);
    $checkedIn = User::factory()->create(['role' => 'student', 'evening_emotion_reminder' => true]);
    $checkedIn->moods()->create(['date' => '2026-11-01', 'mood' => 'Sad', 'note' => 'PRIVATE NOTE']);
    User::factory()->create(['role' => 'student']);
    User::factory()->create(['role' => 'admin', 'evening_emotion_reminder' => true]);
    $eligible->moods()->create(['date' => '2026-10-31', 'mood' => 'Happy']);

    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    $notification = Notification::sole();
    expect($notification->user_id)->toBe($eligible->id)
        ->and($notification->admin_id)->toBeNull()->and($notification->broadcast_id)->toBeNull()
        ->and($notification->task_id)->toBeNull()->and((bool) $notification->is_read)->toBeFalse()
        ->and($notification->title)->toBe('Evening Emotion Check-in')
        ->and($notification->message)->toBe("How was your day? Take a moment to reflect whenever you're ready.")
        ->and($notification->notification_type)->toBe('evening_emotion_reminder')
        ->and($notification->deduplication_key)->toBe('evening_emotion_reminder:'.$eligible->id.':2026-11-01');
    Mail::assertNothingSent(); Mail::assertNothingQueued();
});

test('reruns and opt-out then opt-in do not duplicate the same days reminder', function () {
    $student = User::factory()->create(['role' => 'student', 'evening_emotion_reminder' => true]);
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    $this->actingAs($student)->patch(route('profile.emotion-reminder.update'), ['evening_emotion_reminder' => false]);
    $this->patch(route('profile.emotion-reminder.update'), ['evening_emotion_reminder' => true]);
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    expect(Notification::count())->toBe(1);
});

test('reminder date changes at Malaysian midnight and does not use the UTC date', function () {
    $student = User::factory()->create(['role' => 'student', 'evening_emotion_reminder' => true]);
    $this->travelTo(Carbon::parse('2026-11-01 15:59:00', 'UTC'));
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    $this->travelTo(Carbon::parse('2026-11-01 16:01:00', 'UTC'));
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    expect(Notification::orderBy('id')->pluck('deduplication_key')->all())->toBe([
        'evening_emotion_reminder:'.$student->id.':2026-11-01',
        'evening_emotion_reminder:'.$student->id.':2026-11-02',
    ]);
});

test('eligible students are processed across more than one batch', function () {
    User::factory()->count(105)->create(['role' => 'student', 'evening_emotion_reminder' => true]);
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    expect(Notification::count())->toBe(105);
});

test('reminders create no moods XP profiles or rewards', function () {
    User::factory()->create(['role' => 'student', 'evening_emotion_reminder' => true]);
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    expect(Mood::count())->toBe(0)->and(XpTransaction::count())->toBe(0)->and(GamificationProfile::count())->toBe(0);
    Mail::assertNothingSent(); Mail::assertNothingQueued();
});

test('notification links to check-in and other students cannot see it or change its read state', function () {
    $student = User::factory()->create(['role' => 'student', 'evening_emotion_reminder' => true]);
    $other = User::factory()->create(['role' => 'student']);
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    $notification = Notification::sole();
    $this->actingAs($other)->get(route('notifications.index'))->assertOk()->assertDontSee('Evening Emotion Check-in');
    expect((bool) $notification->fresh()->is_read)->toBeFalse();
    $this->actingAs($student)->get(route('notifications.index'))->assertOk()
        ->assertSee('Evening Emotion Check-in')->assertSee(route('moods.create'));
    expect((bool) $notification->fresh()->is_read)->toBeTrue();
});

test('task reminder delivery and deduplication remain independent', function () {
    $student = User::factory()->create(['role' => 'student', 'evening_emotion_reminder' => true]);
    $task = $student->tasks()->create(['title' => 'Reminder regression', 'due_date' => '2026-11-04', 'priority' => 'High', 'difficulty' => 2, 'status' => 'Pending']);
    $this->artisan('sps:send-task-due-reminders')->assertSuccessful();
    $this->artisan('sps:send-evening-emotion-reminders')->assertSuccessful();
    $this->artisan('sps:send-task-due-reminders')->assertSuccessful();
    expect(Notification::where('notification_type', 'task_due_reminder')->count())->toBe(1)
        ->and(Notification::where('notification_type', 'evening_emotion_reminder')->count())->toBe(1);
    $this->assertDatabaseHas('notifications', ['deduplication_key' => 'task_due_3_days:'.$task->id]);
    Mail::assertSent(TaskDueReminderMail::class, 1);
});

test('both reminders retain their specified Malaysia-local schedules', function () {
    app(Kernel::class)->all();
    $events = collect(app(Schedule::class)->events());
    $task = $events->first(fn ($event) => str_contains($event->command ?? '', 'sps:send-task-due-reminders'));
    $mood = $events->first(fn ($event) => str_contains($event->command ?? '', 'sps:send-evening-emotion-reminders'));
    expect($task)->not->toBeNull()->and($task->expression)->toBe('0 8 * * *')
        ->and($task->timezone)->toBe('Asia/Kuala_Lumpur')
        ->and($mood)->not->toBeNull()->and($mood->expression)->toBe('30 20 * * *')
        ->and($mood->timezone)->toBe('Asia/Kuala_Lumpur');
});
