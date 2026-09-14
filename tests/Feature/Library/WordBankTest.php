<?php

use App\Models\User;
use App\Models\WordBankEntry;
use App\Models\WordBankGroup;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('word-bank.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can browse the word bank', function () {
    $user = User::factory()->create();
    $group = WordBankGroup::factory()->create();
    WordBankEntry::factory()->count(3)->create(['word_bank_group_id' => $group->id]);

    $response = $this->actingAs($user)->get(route('word-bank.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('word-bank/index')
            ->has('groups')
            ->has('entries.data', 3)
            ->where('entries.total', 3),
        );
});

test('word bank entries are paginated 25 per page', function () {
    $user = User::factory()->create();
    $group = WordBankGroup::factory()->create();
    WordBankEntry::factory()->count(30)->create(['word_bank_group_id' => $group->id]);

    $response = $this->actingAs($user)->get(route('word-bank.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('word-bank/index')
            ->has('entries.data', 25)
            ->where('entries.total', 30)
            ->where('entries.last_page', 2),
        );

    $secondPage = $this->actingAs($user)->get(route('word-bank.index', ['page' => 2]));

    $secondPage
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('entries.data', 5),
        );
});

test('the group filter only returns that group\'s entries', function () {
    $user = User::factory()->create();
    $groupA = WordBankGroup::factory()->create();
    $groupB = WordBankGroup::factory()->create();
    WordBankEntry::factory()->count(2)->create(['word_bank_group_id' => $groupA->id]);
    WordBankEntry::factory()->count(4)->create(['word_bank_group_id' => $groupB->id]);

    $response = $this->actingAs($user)->get(route('word-bank.index', ['group' => $groupA->id]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('entries.data', 2)
            ->where('filters.group', $groupA->id),
        );
});

test('the search query narrows results by term', function () {
    $user = User::factory()->create();
    $group = WordBankGroup::factory()->create();
    WordBankEntry::factory()->create(['word_bank_group_id' => $group->id, 'term' => 'apple']);
    WordBankEntry::factory()->create(['word_bank_group_id' => $group->id, 'term' => 'banana']);

    $response = $this->actingAs($user)->get(route('word-bank.index', ['q' => 'apple']));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('entries.data', 1)
            ->where('entries.data.0.term', 'apple')
            ->where('filters.q', 'apple'),
        );
});

test('the search query matches the native meaning and example sentence too', function () {
    $user = User::factory()->create();
    $group = WordBankGroup::factory()->create();
    WordBankEntry::factory()->create([
        'word_bank_group_id' => $group->id,
        'term' => 'run',
        'native_meaning' => 'dauna',
        'example_sentence' => 'I like to run every morning.',
    ]);
    WordBankEntry::factory()->create([
        'word_bank_group_id' => $group->id,
        'term' => 'walk',
        'native_meaning' => 'chalna',
        'example_sentence' => 'She walks to school.',
    ]);

    $byMeaning = $this->actingAs($user)->get(route('word-bank.index', ['q' => 'dauna']));
    $byMeaning->assertInertia(fn (Assert $page) => $page->has('entries.data', 1));

    $bySentence = $this->actingAs($user)->get(route('word-bank.index', ['q' => 'school']));
    $bySentence->assertInertia(fn (Assert $page) => $page->has('entries.data', 1));
});
