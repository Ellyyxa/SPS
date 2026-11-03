<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-03 00:15:00', 'Asia/Kuala_Lumpur'));
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->a = User::factory()->create(['role' => 'student', 'programme' => 'SVM', 'course' => 'Computing', 'semester' => '1']);
    $this->b = User::factory()->create(['role' => 'student', 'programme' => 'DVM', 'course' => 'Business', 'semester' => '2']);
    foreach ([['2026-11-03', 'Happy'], ['2026-11-01', 'Neutral'], ['2026-10-28', 'Happy'], ['2026-10-05', 'Sad'], ['2026-01-01', 'Stress'], ['2025-12-31', 'Angry']] as [$date, $mood]) {
        $this->a->moods()->create(['date' => $date, 'mood' => $mood, 'note' => 'PRIVATE_TREND_SENTINEL']);
    }
    $this->b->moods()->create(['date' => '2026-11-03', 'mood' => 'Sad', 'note' => 'PRIVATE_OTHER_SENTINEL']);
});

afterEach(function () { $this->travelBack(); });

test('short periods use real Malaysian dates and explicit no-record buckets', function ($period, $first, $days, $entries) {
    $response = $this->actingAs($this->admin)->get(route('admin.emotions.index', ['period' => $period]))->assertOk();
    $analytics = $response->viewData('analytics');
    $series = collect($analytics['series'])->keyBy('date');
    expect($analytics['mode'])->toBe('daily')
        ->and($series)->toHaveCount($days)
        ->and($series->keys()->first())->toBe($first)
        ->and($series->keys()->last())->toBe('2026-11-03')
        ->and($series['2026-11-02']['entries'])->toBe(0)
        ->and($series['2026-11-02']['participants'])->toBe(0)
        ->and(array_sum($series['2026-11-02']['counts']))->toBe(0)
        ->and($series['2026-11-03']['participants'])->toBe(2)
        ->and($response->viewData('entries'))->toBe($entries)
        ->and($response->viewData('moods')->total())->toBe($entries)
        ->and($response->viewData('distribution')->sum())->toBe($entries);
})->with([['7', '2026-10-28', 7, 4], ['30', '2026-10-05', 30, 5]]);

test('year aggregation distinguishes repeated entries from unique monthly participants', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.emotions.index', ['period' => 'year']))->assertOk();
    $analytics = $response->viewData('analytics');
    $series = collect($analytics['series'])->keyBy('date');
    expect($analytics['mode'])->toBe('monthly')
        ->and($series)->toHaveCount(11)
        ->and($series['2026-11']['entries'])->toBe(3)
        ->and($series['2026-11']['participants'])->toBe(2)
        ->and($series['2026-10']['entries'])->toBe(2)
        ->and($series['2026-10']['participants'])->toBe(1)
        ->and($response->viewData('overview')['participants'])->toBe(2)
        ->and($response->viewData('entries'))->toBe(6)
        ->and($response->viewData('moods')->total())->toBe(6);
    $response->assertSee('not verified historical enrollment')->assertDontSee('Participation (current accounts)');
});

test('multi-year ranges and combined cohort filters constrain every data source', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.emotions.index', [
        'period' => 'custom', 'start_date' => '2025-12-31', 'end_date' => '2026-11-01',
        'programme' => 'SVM', 'course' => 'Computing', 'semester' => '1',
    ]))->assertOk()->assertDontSee('PRIVATE_TREND_SENTINEL')->assertDontSee('PRIVATE_OTHER_SENTINEL');
    $series = collect($response->viewData('analytics')['series'])->keyBy('date');
    expect($series['2025-12']['counts']['Angry'])->toBe(1)
        ->and($series['2026-11']['entries'])->toBe(1)
        ->and($response->viewData('entries'))->toBe(5)
        ->and($response->viewData('distribution')->sum())->toBe(5)
        ->and($response->viewData('overview')['total'])->toBe(1)
        ->and($response->viewData('overview')['participants'])->toBe(1)
        ->and($response->viewData('moods')->pluck('user_id')->unique()->all())->toBe([$this->a->id])
        ->and($response->viewData('moods')->first()->date->toDateString())->toBe('2026-11-01');
});

test('trend SQL and chart payloads exclude private notes and identities', function () {
    $queries = [];
    DB::listen(function ($event) use (&$queries) { if (str_contains($event->sql, 'moods')) $queries[] = $event->sql; });
    $response = $this->actingAs($this->admin)->get(route('admin.emotions.index', ['period' => 'year']))->assertOk();
    foreach ($queries as $sql) expect($sql)->not->toContain('"note"', '`note`');
    expect(collect($queries)->filter(fn ($sql) => str_contains(strtolower($sql), 'group by'))->count())->toBeGreaterThanOrEqual(3);
    $dom = new DOMDocument();
    @$dom->loadHTML($response->getContent());
    foreach ((new DOMXPath($dom))->query('//script[@type="application/json"]') as $script) {
        $payload = $script->textContent;
        json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        expect($payload)->not->toContain('PRIVATE_', '"note"', '"user_id"', '"name"', '"student_id"');
    }
    expect($this->a->moods()->first()->note)->toBe('PRIVATE_TREND_SENTINEL');
});

test('invalid custom ranges are rejected', function ($extra, $field) {
    $this->actingAs($this->admin)->get(route('admin.emotions.index', array_merge(['period' => 'custom'], $extra)))
        ->assertRedirect()->assertSessionHasErrors($field);
})->with([
    [[], 'start_date'],
    [['start_date' => 'bad', 'end_date' => '2026-11-03'], 'start_date'],
    [['start_date' => '2026-11-03', 'end_date' => '2026-11-02'], 'end_date'],
    [['start_date' => '2026-11-03', 'end_date' => '2026-11-04'], 'end_date'],
    [['start_date' => '2020-11-03', 'end_date' => '2025-11-03'], 'end_date'],
]);

test('five-year boundary is accepted and bounded to monthly buckets', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.emotions.index', [
        'period' => 'custom', 'start_date' => '2021-11-04', 'end_date' => '2026-11-03',
    ]))->assertOk();
    expect($response->viewData('analytics')['series'])->toHaveCount(61);
});

test('empty long period keeps calendar buckets without invented emotions', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.emotions.index', ['period' => 'year', 'course' => 'Unknown']))->assertOk();
    expect($response->viewData('entries'))->toBe(0)
        ->and($response->viewData('overview')['total'])->toBe(0)
        ->and(collect($response->viewData('analytics')['series'])->sum('entries'))->toBe(0);
    $response->assertSee('No check-ins recorded in this period and cohort.');
});

test('long period routes retain admin authorization', function () {
    $url = route('admin.emotions.index', ['period' => 'year']);
    $this->get($url)->assertRedirect(route('login'));
    $this->actingAs($this->a)->get($url)->assertForbidden();
});

test('long period history stays paginated with active filters', function () {
    for ($day = 1; $day <= 20; $day++) $this->b->moods()->create(['date' => sprintf('2026-09-%02d', $day), 'mood' => 'Happy']);
    $response = $this->actingAs($this->admin)->get(route('admin.emotions.index', ['period' => 'year', 'programme' => 'DVM']))->assertOk();
    expect($response->viewData('moods')->count())->toBe(15)
        ->and($response->viewData('moods')->total())->toBe(21)
        ->and($response->viewData('moods')->nextPageUrl())->toContain('period=year', 'programme=DVM');
});
