<?php

use App\Enums\SkillArea;
use App\Models\DayTask;
use App\Models\Tense;

test('the grammar() factory state always produces a Grammar task with a resolvable tense', function () {
    $task = DayTask::factory()->grammar()->create();

    expect($task->type)->toBe(SkillArea::Grammar);
    expect($task->tense_id)->not->toBeNull();
    expect($task->tense)->toBeInstanceOf(Tense::class);
});

test('the default factory state never produces a Grammar task, and always has a null tense_id', function () {
    // Inspect raw definition() output rather than persisting — persisting
    // enough samples to make a random exclusion check meaningful would spin
    // up more Month/Week rows than their fake()->unique() ranges allow, and
    // isn't necessary: the pool the factory draws from deterministically
    // excludes Grammar, so this holds on every draw, not just on average.
    $definitions = collect(range(1, 30))->map(fn () => DayTask::factory()->definition());

    expect($definitions->pluck('type'))->not->toContain(SkillArea::Grammar);
    expect($definitions->pluck('tense_id'))->each->toBeNull();
});
