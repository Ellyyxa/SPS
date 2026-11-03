<?php

use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2027-01-03 00:30:00', 'Asia/Kuala_Lumpur'));
    $this->student = User::factory()->create(['role' => 'student']);
});

afterEach(function () {
    $this->travelBack();
});

test('7 and 30 day filters include their Malaysian calendar boundaries', function ($period, $first, $outside, $length) {
    foreach ([$first, '2027-01-03', $outside] as $date) {
        $mood = $this->student->moods()->create(['date' => $date, 'mood' => 'Happy']);
        $mood->forceFill(['created_at' => '2025-01-01 12:00:00'])->save();
    }
    $response = $this->actingAs($this->student)->get(route('moods.index', ['period' => $period]))->assertOk();
    expect($response->viewData('moods')->total())->toBe(2)
        ->and($response->viewData('start')->toDateString())->toBe($first)
        ->and($response->viewData('end')->toDateString())->toBe('2027-01-03');
    $series = $response->viewData('historyChart')['series'];
    expect($series)->toHaveCount($length)
        ->and($series[0]['date'])->toBe($first)
        ->and($series[$length - 1]['date'])->toBe('2027-01-03');
})->with([
    '7 days' => ['7', '2026-12-28', '2026-12-27', 7],
    '30 days' => ['30', '2026-12-05', '2026-12-04', 30],
]);

test('missing days have explicit null moods and no invented category', function () {
    $this->student->moods()->create(['date' => '2026-12-28', 'mood' => 'Happy']);
    $this->student->moods()->create(['date' => '2026-12-30', 'mood' => 'Sad']);
    $response = $this->actingAs($this->student)->get(route('moods.index'))->assertOk();
    $series = $response->viewData('historyChart')['series'];
    expect($series[0]['mood'])->toBe('Happy')
        ->and($series[1]['date'])->toBe('2026-12-29')
        ->and($series[1]['mood'])->toBeNull()
        ->and($series[2]['mood'])->toBe('Sad')
        ->and($series[6]['mood'])->toBeNull();
});

test('this year excludes earlier years and uses monthly category counts', function () {
    foreach (['2026-12-31', '2027-01-01', '2027-01-03'] as $date) {
        $this->student->moods()->create(['date' => $date, 'mood' => 'Neutral']);
    }
    $response = $this->actingAs($this->student)->get(route('moods.index', ['period' => 'year']))->assertOk();
    $chart = $response->viewData('historyChart');
    expect($response->viewData('moods')->total())->toBe(2)
        ->and($chart['mode'])->toBe('monthly')
        ->and($chart['series'])->toHaveCount(1)
        ->and($chart['series'][0]['label'])->toBe('Jan 2027')
        ->and($chart['series'][0]['counts'])->toBe(['Happy' => 0, 'Neutral' => 2, 'Sad' => 0, 'Stress' => 0, 'Angry' => 0]);
});

test('custom history orders represented dates across years rather than creation timestamps', function () {
    $older = $this->student->moods()->create(['date' => '2025-12-31', 'mood' => 'Happy', 'note' => 'Older reflection']);
    $older->forceFill(['created_at' => '2027-01-03 00:00:00'])->save();
    $newer = $this->student->moods()->create(['date' => '2026-01-01', 'mood' => 'Sad']);
    $newer->forceFill(['created_at' => '2026-01-01 00:00:00'])->save();
    $this->student->moods()->create(['date' => '2026-01-02', 'mood' => 'Angry']);
    $response = $this->actingAs($this->student)->get(route('moods.index', [
        'period' => 'custom', 'start_date' => '2025-12-31', 'end_date' => '2026-01-01',
    ]))->assertOk()->assertSee('31 Dec 2025')->assertSee('01 Jan 2026')->assertSee('Older reflection');
    expect($response->viewData('moods')->getCollection()->pluck('id')->all())->toBe([$newer->id, $older->id])
        ->and($response->viewData('historyChart')['series'])->toHaveCount(2);
});

test('custom ranges reject missing invalid reversed future and excessive ranges', function ($query, $field) {
    $this->actingAs($this->student)->from(route('moods.index'))->get(route('moods.index', $query))
        ->assertRedirect(route('moods.index'))->assertSessionHasErrors($field);
})->with([
    'missing dates' => [['period' => 'custom'], 'start_date'],
    'invalid date' => [['period' => 'custom', 'start_date' => '2026-02-30', 'end_date' => '2026-03-01'], 'start_date'],
    'reversed' => [['period' => 'custom', 'start_date' => '2026-12-31', 'end_date' => '2026-12-01'], 'end_date'],
    'future' => [['period' => 'custom', 'start_date' => '2027-01-01', 'end_date' => '2027-01-04'], 'end_date'],
    'too long' => [['period' => 'custom', 'start_date' => '2020-01-01', 'end_date' => '2027-01-03'], 'end_date'],
    'invalid period' => [['period' => 'everything'], 'period'],
]);

test('student history and chart never include another students records or notes', function () {
    $other = User::factory()->create(['role' => 'student']);
    $other->moods()->create(['date' => '2027-01-03', 'mood' => 'Angry', 'note' => 'PRIVATE OTHER STUDENT NOTE']);
    $this->student->moods()->create(['date' => '2027-01-02', 'mood' => 'Happy', 'note' => 'My private note']);
    $response = $this->actingAs($this->student)->get(route('moods.index'))
        ->assertOk()->assertDontSee('PRIVATE OTHER STUDENT NOTE')->assertSee('My private note');
    expect($response->viewData('moods')->total())->toBe(1)
        ->and(collect($response->viewData('historyChart')['series'])->pluck('mood')->filter()->all())->toBe([5 => 'Happy'])
        ->and(json_encode($response->viewData('historyChart')))->not->toContain('My private note', 'PRIVATE OTHER STUDENT NOTE');
});

test('history is paginated while chart covers only the bounded selected period', function () {
    for ($offset = 0; $offset < 40; $offset++) {
        $this->student->moods()->create(['date' => Carbon::today()->subDays($offset)->toDateString(), 'mood' => 'Happy']);
    }
    $response = $this->actingAs($this->student)->get(route('moods.index', ['period' => '30']))->assertOk();
    $moods = $response->viewData('moods');
    expect($moods->total())->toBe(30)->and($moods->count())->toBe(15)
        ->and($moods->nextPageUrl())->toContain('period=30', 'page=2')
        ->and($response->viewData('historyChart')['series'])->toHaveCount(30);
    $page2 = $this->get(route('moods.index', ['period' => '30', 'page' => 2]))->assertOk()->viewData('moods');
    expect($page2->count())->toBe(15)
        ->and($moods->getCollection()->pluck('id')->intersect($page2->getCollection()->pluck('id'))->count())->toBe(0);
});

test('multi-year custom chart contains bounded monthly summaries rather than raw history', function () {
    $this->student->moods()->create(['date' => '2024-01-01', 'mood' => 'Happy']);
    $this->student->moods()->create(['date' => '2026-12-31', 'mood' => 'Sad']);
    $response = $this->actingAs($this->student)->get(route('moods.index', [
        'period' => 'custom', 'start_date' => '2024-01-01', 'end_date' => '2026-12-31',
    ]))->assertOk();
    $chart = $response->viewData('historyChart');
    expect($chart['mode'])->toBe('monthly')->and($chart['series'])->toHaveCount(36)
        ->and($chart['series'][0]['counts']['Happy'])->toBe(1)
        ->and($chart['series'][1]['counts']['Happy'])->toBe(0)
        ->and($chart['series'][35]['counts']['Sad'])->toBe(1);
});

test('history is accessible without a mood today and guests remain blocked', function () {
    $this->get(route('moods.index'))->assertRedirect(route('login'));
    $this->actingAs($this->student)->get(route('moods.index'))->assertOk()->assertSee('No emotion history in this period.');
});
