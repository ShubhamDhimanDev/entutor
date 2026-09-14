<?php

use App\Enums\MistakeTag;
use App\Models\CommonMistake;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('mistakes.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can browse common mistakes', function () {
    $user = User::factory()->create();
    CommonMistake::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('mistakes.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('mistakes/index')
            ->has('tags', 7)
            ->has('mistakes.data', 3),
        );
});

test('the tag filter only returns mistakes with that tag', function () {
    $user = User::factory()->create();
    CommonMistake::factory()->count(2)->create(['tag' => MistakeTag::Tense]);
    CommonMistake::factory()->count(3)->create(['tag' => MistakeTag::Articles]);

    $response = $this->actingAs($user)->get(route('mistakes.index', ['tag' => MistakeTag::Tense->value]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.tag', MistakeTag::Tense->value)
            ->has(
                'mistakes.data',
                2,
                fn (Assert $mistake) => $mistake->where('tag', MistakeTag::Tense->value)->etc(),
            ),
        );
});

test('an invalid tag value is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('mistakes.index', ['tag' => 'not-a-real-tag']));

    $response->assertSessionHasErrors('tag');
});
