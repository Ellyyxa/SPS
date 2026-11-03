<?php

use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-01 00:30:00', 'Asia/Kuala_Lumpur'));
    $this->student = User::factory()->create(['role' => 'student']);
});

afterEach(function () {
    $this->travelBack();
});

function moodPageDom(string $html): DOMXPath
{
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
    return new DOMXPath($dom);
}

test('check-in page renders Malaysian today and yesterday with Today selected', function () {
    $response = $this->actingAs($this->student)->get(route('moods.create'))
        ->assertOk()->assertSee('Evening Emotion Check-in')->assertSee('Take a moment to reflect on your day.');
    $xpath = moodPageDom($response->getContent());
    expect($xpath->evaluate('string(//select[@name="date"]/option[@selected]/@value)'))->toBe('2026-11-01')
        ->and($xpath->evaluate('count(//select[@name="date"]/option)'))->toBe(2.0)
        ->and($xpath->evaluate('string(//option[@value="2026-10-31"])'))->toContain('Yesterday', '31 Oct 2026')
        ->and($xpath->evaluate('count(//button[@data-checkin-save][@disabled])'))->toBe(0.0)
        ->and($xpath->evaluate('count(//form[@data-checkin-form]/input[@name="_token"])'))->toBe(1.0);
    expect($this->student->moods()->count())->toBe(0)->and($this->student->xpTransactions()->count())->toBe(0);
});

test('recorded flags belong only to the authenticated student and both dates remain selectable', function ($recordedDate) {
    $this->student->moods()->create(['date' => $recordedDate, 'mood' => 'Happy']);
    $other = User::factory()->create(['role' => 'student']);
    $otherDate = $recordedDate === '2026-11-01' ? '2026-10-31' : '2026-11-01';
    $other->moods()->create(['date' => $otherDate, 'mood' => 'Sad']);
    $response = $this->actingAs($this->student)->get(route('moods.create'))->assertOk();
    $xpath = moodPageDom($response->getContent());
    expect($xpath->evaluate('string(//option[@value="'.$recordedDate.'"]/@data-recorded)'))->toBe('true')
        ->and($xpath->evaluate('string(//option[@value="'.$otherDate.'"]/@data-recorded)'))->toBe('false')
        ->and($xpath->evaluate('count(//select[@name="date"]/option[@disabled])'))->toBe(0.0)
        ->and($xpath->evaluate('count(//button[@data-checkin-save][@disabled])'))->toBe($recordedDate === '2026-11-01' ? 1.0 : 0.0);
})->with(['today' => '2026-11-01', 'yesterday' => '2026-10-31']);

test('when both days are recorded the form offers Not Now and blocks Save', function () {
    foreach (['2026-11-01', '2026-10-31'] as $date) {
        $this->student->moods()->create(['date' => $date, 'mood' => 'Happy']);
    }
    $response = $this->actingAs($this->student)->get(route('moods.create'))->assertOk();
    $xpath = moodPageDom($response->getContent());
    expect($xpath->evaluate('count(//option[@data-recorded="true"])'))->toBe(2.0)
        ->and($xpath->evaluate('count(//button[@data-checkin-save][@disabled])'))->toBe(1.0)
        ->and($xpath->evaluate('string(//form[@data-checkin-form]//a/@href)'))->toBe(route('dashboard'));
});

test('Yesterday and notes survive a validation redirect without switching to Today', function () {
    $response = $this->actingAs($this->student)->followingRedirects()->from(route('moods.create'))->post(route('moods.store'), [
        'date' => '2026-10-31', 'mood' => 'Happy', 'note' => str_repeat('a', 2001),
    ])->assertOk()->assertSee('Please review your check-in.');
    $xpath = moodPageDom($response->getContent());
    expect($xpath->evaluate('string(//select[@name="date"]/option[@selected]/@value)'))->toBe('2026-10-31')
        ->and($xpath->evaluate('count(//input[@name="mood"][@value="Happy"][@checked])'))->toBe(1.0)
        ->and($xpath->evaluate('string(//textarea[@name="note"])'))->toBe(str_repeat('a', 2001));
});

test('an expired Yesterday submission displays the error and preserves its old date at midnight', function () {
    $this->travelTo(Carbon::parse('2026-11-02 00:01:00', 'Asia/Kuala_Lumpur'));
    $response = $this->actingAs($this->student)->followingRedirects()->from(route('moods.create'))->post(route('moods.store'), [
        'date' => '2026-10-31', 'mood' => 'Neutral', 'note' => 'Late reflection',
    ])->assertOk()
        ->assertSee('You can check in only for today or yesterday in Malaysia time.');
    $xpath = moodPageDom($response->getContent());
    expect($xpath->evaluate('string(//select[@name="date"]/option[@selected]/@value)'))->toBe('2026-10-31')
        ->and($xpath->evaluate('count(//option[@selected][@data-unavailable="true"])'))->toBe(1.0)
        ->and($xpath->evaluate('count(//button[@data-checkin-save][@disabled])'))->toBe(1.0)
        ->and($this->student->moods()->count())->toBe(0)
        ->and($this->student->xpTransactions()->count())->toBe(0);
});

test('student-only page rejects admin access', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->get(route('moods.create'))->assertForbidden();
});
