<?php

use App\Models\GamificationStreakDay;
use App\Models\Mood;
use App\Models\User;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Malaysian midnight is still the previous UTC calendar day.
    $this->travelTo(Carbon::parse('2026-11-01 00:30:00', 'Asia/Kuala_Lumpur'));
    $this->student = User::factory()->create(['role' => 'student']);
});

afterEach(function () {
    $this->travelBack();
});

test('student can check in for today or yesterday with a separate submission timestamp', function ($date) {
    $other = User::factory()->create(['role' => 'student']);
    $this->actingAs($this->student)->post(route('moods.store'), [
        'date' => $date, 'mood' => 'Happy', 'note' => 'Reflection', 'user_id' => $other->id,
    ])->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();

    $mood = $this->student->moods()->sole();
    expect($mood->date->toDateString())->toBe($date)
        ->and($mood->created_at->toDateString())->toBe('2026-11-01')
        ->and($other->moods()->count())->toBe(0)
        ->and($this->student->gamificationProfile->total_xp)->toBe(5);
    $this->assertDatabaseHas('xp_transactions', [
        'user_id' => $this->student->id, 'source_type' => 'mood_checkin', 'xp_amount' => 5,
        'reward_key' => 'mood_checkin_date:'.$this->student->id.':'.$date,
    ]);
})->with(['today' => '2026-11-01', 'yesterday' => '2026-10-31']);

test('the unchanged form can omit date and defaults to Malaysian today', function () {
    $this->actingAs($this->student)->post(route('moods.store'), ['mood' => 'Neutral'])
        ->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();
    expect($this->student->moods()->sole()->date->toDateString())->toBe('2026-11-01');
});

test('same date duplicates do not alter the original mood or award more XP', function () {
    $this->actingAs($this->student)->post(route('moods.store'), ['date' => '2026-11-01', 'mood' => 'Happy']);
    $this->post(route('moods.store'), ['date' => '2026-11-01', 'mood' => 'Sad'])
        ->assertRedirect(route('dashboard'))->assertSessionHas('error');
    expect($this->student->moods()->count())->toBe(1)
        ->and($this->student->moods()->sole()->mood)->toBe('Happy')
        ->and($this->student->gamificationProfile->total_xp)->toBe(5)
        ->and($this->student->xpTransactions()->count())->toBe(1);
});

test('future old malformed and empty dates are rejected without rewards', function ($date) {
    $this->actingAs($this->student)->post(route('moods.store'), ['date' => $date, 'mood' => 'Happy'])
        ->assertSessionHasErrors('date');
    expect($this->student->moods()->count())->toBe(0)
        ->and($this->student->xpTransactions()->count())->toBe(0);
})->with(['future' => '2026-11-02', 'too old' => '2026-10-30', 'format' => '31/10/2026', 'empty' => '']);

test('invalid moods and notes are rejected without rewards', function ($input, $field) {
    $this->actingAs($this->student)->post(route('moods.store'), $input)->assertSessionHasErrors($field);
    expect($this->student->moods()->count())->toBe(0)
        ->and($this->student->xpTransactions()->count())->toBe(0);
})->with([
    'unknown mood' => [['mood' => 'Excited'], 'mood'],
    'array mood' => [['mood' => ['Happy']], 'mood'],
    'array note' => [['mood' => 'Happy', 'note' => ['note']], 'note'],
    'long note' => [['mood' => 'Happy', 'note' => str_repeat('a', 2001)], 'note'],
]);

test('admins and guests cannot submit moods', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->post(route('moods.store'), ['mood' => 'Happy'])->assertRedirect(route('login'));
    $this->actingAs($admin)->post(route('moods.store'), ['mood' => 'Happy'])->assertForbidden();
    expect(Mood::count())->toBe(0)->and(XpTransaction::count())->toBe(0);
});

test('today followed by yesterday awards each date once and preserves the latest streak date', function () {
    $this->actingAs($this->student);
    foreach (['2026-11-01', '2026-10-31', '2026-11-01', '2026-10-31'] as $date) {
        $this->post(route('moods.store'), ['date' => $date, 'mood' => 'Neutral'])->assertRedirect(route('dashboard'));
    }
    $profile = $this->student->gamificationProfile;
    expect($profile->total_xp)->toBe(10)
        ->and($profile->current_streak)->toBe(2)
        ->and($profile->longest_streak)->toBe(2)
        ->and($profile->last_qualifying_activity_date->toDateString())->toBe('2026-11-01')
        ->and($this->student->moods()->count())->toBe(2)
        ->and($this->student->xpTransactions()->count())->toBe(2);
});

test('replacement of a newly rewarded mood cannot award the date again', function () {
    $this->actingAs($this->student)->post(route('moods.store'), ['date' => '2026-10-31', 'mood' => 'Happy']);
    // Only the isolated test database is changed; the application exposes no replacement action.
    $this->student->moods()->delete();
    $this->post(route('moods.store'), ['date' => '2026-10-31', 'mood' => 'Neutral'])->assertSessionHasNoErrors();
    expect($this->student->moods()->count())->toBe(1)
        ->and($this->student->xpTransactions()->count())->toBe(1)
        ->and($this->student->gamificationProfile->total_xp)->toBe(5);
});

test('legacy moods and their rewards are left unchanged on duplicate submission', function () {
    $mood = $this->student->moods()->create(['date' => '2026-10-31', 'mood' => 'Sad']);
    app(GamificationService::class)->award($this->student, 5, 'Daily Emotion Check-In', 'mood_checkin', $mood->id, 'mood_checkin:'.$mood->id);
    $this->actingAs($this->student)->post(route('moods.store'), ['date' => '2026-10-31', 'mood' => 'Happy'])->assertSessionHas('error');
    expect($mood->fresh()->mood)->toBe('Sad')
        ->and($this->student->xpTransactions()->count())->toBe(1)
        ->and($this->student->gamificationProfile->total_xp)->toBe(5);
});

test('database unique constraint independently rejects duplicate mood dates', function () {
    $data = ['date' => '2026-11-01', 'mood' => 'Happy'];
    $this->student->moods()->create($data);
    expect(fn () => $this->student->moods()->create($data))->toThrow(UniqueConstraintViolationException::class);
});

test('failed gamification rolls back the mood and qualifying day', function () {
    $this->mock(GamificationService::class)->shouldReceive('award')->once()->andThrow(new RuntimeException('Reward failed'));
    $this->withoutExceptionHandling();
    expect(fn () => $this->actingAs($this->student)->post(route('moods.store'), ['mood' => 'Happy']))
        ->toThrow(RuntimeException::class, 'Reward failed');
    expect($this->student->moods()->count())->toBe(0)
        ->and($this->student->xpTransactions()->count())->toBe(0)
        ->and(GamificationStreakDay::count())->toBe(0);
});

test('Not Now dashboard navigation creates no mood or XP reward', function () {
    $this->actingAs($this->student)->get(route('dashboard'))->assertOk();
    expect($this->student->moods()->count())->toBe(0)
        ->and($this->student->xpTransactions()->count())->toBe(0)
        ->and($this->student->gamificationProfile->total_xp)->toBe(0);
    $progress = app(GamificationService::class)->progress($this->student);
    expect($progress['level']['number'])->toBe(1)->and($progress['percent'])->toBe(0.0);
});

test('a late day bridges a gap and unlocks the seven day bonus only once', function () {
    $service = app(GamificationService::class);
    // Qualifying-day ledger only: no mood/task history is backfilled.
    foreach (['2026-10-26', '2026-10-27', '2026-10-28', '2026-10-29', '2026-10-30', '2026-11-01'] as $date) {
        $service->qualifyDay($this->student, Carbon::parse($date));
    }
    $this->actingAs($this->student)->post(route('moods.store'), ['date' => '2026-10-31', 'mood' => 'Happy']);
    $this->post(route('moods.store'), ['date' => '2026-10-31', 'mood' => 'Happy']);
    $profile = $this->student->gamificationProfile;
    expect($profile->total_xp)->toBe(80)
        ->and($profile->current_streak)->toBe(7)
        ->and($profile->longest_streak)->toBe(7)
        ->and($profile->last_qualifying_activity_date->toDateString())->toBe('2026-11-01')
        ->and($this->student->achievements()->where('achievement_key', 'consistency_star')->count())->toBe(1);
});

test('task completion XP stays idempotent and shares the same daily streak', function () {
    $task = $this->student->tasks()->create([
        'title' => 'Task regression', 'due_date' => '2026-11-03', 'priority' => 'High',
        'difficulty' => 3, 'status' => 'Pending', 'priority_score' => 100,
    ]);
    $this->actingAs($this->student)->post(route('moods.store'), ['date' => '2026-10-31', 'mood' => 'Happy'])
        ->assertRedirect(route('dashboard'))->assertSessionHasNoErrors()->assertSessionHas('success');
    expect($this->student->xpTransactions()->sum('xp_amount'))->toBe(5);
    $this->patch(route('tasks.complete', $task))->assertRedirect(route('tasks.index'));
    $this->patch(route('tasks.complete', $task))->assertRedirect(route('tasks.index'));
    expect($this->student->xpTransactions()->sum('xp_amount'))->toBe(50);
    $this->post(route('moods.store'), ['date' => '2026-11-01', 'mood' => 'Happy'])
        ->assertRedirect(route('dashboard'))->assertSessionHasNoErrors()->assertSessionHas('success');
    // Inspect persisted ledger/profile, rather than accepting a stale relationship value.
    expect($this->student->xpTransactions()->sum('xp_amount'))->toBe(55)
        ->and($this->student->gamificationProfile()->firstOrFail()->total_xp)->toBe(55);
    $this->assertDatabaseHas('xp_transactions', [
        'user_id' => $this->student->id, 'xp_amount' => 5,
        'reward_key' => 'mood_checkin_date:'.$this->student->id.':2026-11-01',
    ]);
    $profile = $this->student->gamificationProfile;
    expect($task->fresh()->status)->toBe('Completed')
        ->and($profile->total_xp)->toBe(55)
        ->and($profile->current_streak)->toBe(2)
        ->and($this->student->xpTransactions()->where('source_type', 'task_completion')->count())->toBe(1)
        ->and($this->student->xpTransactions()->where('source_type', 'task_early_bonus')->count())->toBe(1)
        ->and(GamificationStreakDay::where('user_id', $this->student->id)->count())->toBe(2);
});

test('an inserted day does not reduce a preserved longest streak', function () {
    $service = app(GamificationService::class);
    $profile = $service->profile($this->student);
    $profile->update(['longest_streak' => 12]);
    $this->actingAs($this->student)->post(route('moods.store'), ['date' => '2026-11-01', 'mood' => 'Happy']);
    expect($profile->fresh()->longest_streak)->toBe(12);
});

test('new profile instance has the same zero counters as its stored row', function () {
    $profile = app(GamificationService::class)->profile($this->student);
    expect($profile->total_xp)->toBe(0)
        ->and($profile->current_streak)->toBe(0)
        ->and($profile->longest_streak)->toBe(0)
        ->and($profile->fresh()->total_xp)->toBe(0);
});

test('qualifying the same calendar day twice is a no-op', function () {
    $service = app(GamificationService::class);
    $service->qualifyDay($this->student, Carbon::today());
    $service->qualifyDay($this->student, Carbon::today());
    expect(GamificationStreakDay::where('user_id', $this->student->id)->count())->toBe(1)
        ->and($this->student->gamificationProfile->current_streak)->toBe(1)
        ->and($this->student->gamificationProfile->total_xp)->toBe(0);
});

test('a propagated unique violation becomes friendly only when the same dated mood exists', function () {
    $mood = $this->student->moods()->create(['date' => '2026-11-01', 'mood' => 'Neutral']);
    $failure = new UniqueConstraintViolationException('sqlite', 'insert into moods', [], new PDOException('Simulated concurrent insert'));
    // Simulate the transaction propagating a competing insert failure after rollback.
    DB::partialMock()->shouldReceive('transaction')->once()->andThrow($failure);
    $this->actingAs($this->student)->post(route('moods.store'), ['date' => '2026-11-01', 'mood' => 'Happy'])
        ->assertRedirect(route('dashboard'))->assertSessionHas('error');
    expect($mood->fresh()->mood)->toBe('Neutral')
        ->and($this->student->moods()->count())->toBe(1)
        ->and($this->student->xpTransactions()->count())->toBe(0);
});

test('an unrelated unique failure is rethrown rather than reported as a duplicate mood', function () {
    $failure = new UniqueConstraintViolationException('sqlite', 'insert into xp_transactions', [], new PDOException('Unrelated unique violation'));
    DB::partialMock()->shouldReceive('transaction')->once()->andThrow($failure);
    $this->withoutExceptionHandling();
    expect(fn () => $this->actingAs($this->student)->post(route('moods.store'), ['mood' => 'Happy']))
        ->toThrow(UniqueConstraintViolationException::class);
    expect($this->student->moods()->count())->toBe(0);
});
