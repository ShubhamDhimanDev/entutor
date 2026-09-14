<?php

use App\Enums\RoleplayDomain;
use App\Enums\RoleplaySide;
use App\Models\RoleplayLine;
use App\Models\RoleplayScenario;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $scenario = RoleplayScenario::factory()->create();

    $this->get(route('roleplay.index'))->assertRedirect(route('login'));
    $this->get(route('roleplay.show', $scenario))->assertRedirect(route('login'));
});

test('authenticated users can browse roleplay scenarios as a list', function () {
    $user = User::factory()->create();
    RoleplayScenario::factory()->count(3)->create();

    $response = $this->actingAs($user)->get(route('roleplay.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('roleplay/index')
            ->has('domains', 5)
            ->has('scenarios', 3),
        );
});

test('the domain filter only returns scenarios in that domain', function () {
    $user = User::factory()->create();
    RoleplayScenario::factory()->count(2)->create(['domain' => RoleplayDomain::ShoppingMoney]);
    RoleplayScenario::factory()->count(4)->create(['domain' => RoleplayDomain::SocialFamily]);

    $response = $this->actingAs($user)->get(route('roleplay.index', ['domain' => RoleplayDomain::ShoppingMoney->value]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('scenarios', 2)
            ->where('filters.domain', RoleplayDomain::ShoppingMoney->value),
        );
});

test('the show route returns the full dialogue in order with the tip', function () {
    $user = User::factory()->create();
    $scenario = RoleplayScenario::factory()->create(['tip' => 'Speak slowly and clearly.']);

    RoleplayLine::factory()->create([
        'roleplay_scenario_id' => $scenario->id,
        'side' => RoleplaySide::A,
        'order' => 2,
        'line_text' => 'second line',
    ]);
    RoleplayLine::factory()->create([
        'roleplay_scenario_id' => $scenario->id,
        'side' => RoleplaySide::B,
        'order' => 1,
        'line_text' => 'first line',
    ]);
    RoleplayLine::factory()->create([
        'roleplay_scenario_id' => $scenario->id,
        'side' => RoleplaySide::A,
        'order' => 3,
        'line_text' => 'third line',
    ]);

    $response = $this->actingAs($user)->get(route('roleplay.show', $scenario));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('roleplay/show')
            ->where('scenario.tip', 'Speak slowly and clearly.')
            ->has('scenario.lines', 3)
            ->where('scenario.lines.0.line_text', 'first line')
            ->where('scenario.lines.1.line_text', 'second line')
            ->where('scenario.lines.2.line_text', 'third line'),
        );
});

test('the index list view does not include the full dialogue', function () {
    $user = User::factory()->create();
    $scenario = RoleplayScenario::factory()->create();
    RoleplayLine::factory()->count(4)->create(['roleplay_scenario_id' => $scenario->id]);

    $response = $this->actingAs($user)->get(route('roleplay.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('scenarios.0', fn (Assert $scenario) => $scenario
            ->has('id')
            ->has('title')
            ->has('domain')
            ->has('tip_preview')
            ->etc(),
        ),
    );
});
